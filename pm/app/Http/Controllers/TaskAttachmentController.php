<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\TaskAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskAttachmentController extends Controller
{
    /**
     * Upload file attachment
     */
    public function store(Request $request, $cardId)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        $card = Card::findOrFail($cardId);
        
        // Check if user can work on this task
        if (!$card->canWorkOn(auth()->user())) {
            return redirect()->back()->with('error', 'You are not authorized to upload files to this task. Only assigned members can upload attachments.');
        }
        
        // Check if task is locked
        if ($card->isLocked()) {
            return redirect()->back()->with('error', 'This task is locked. File uploads are disabled.');
        }
        
        // Check project access and not archived
        if ($card->board->project->is_archived) {
            return redirect()->back()->with('error', 'Cannot upload files to archived project.');
        }
        
        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('attachments', $fileName, 'public');

        TaskAttachment::create([
            'card_id' => $card->card_id,
            'uploaded_by' => auth()->id(),
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize()
        ]);

        return redirect()->back()->with('success', 'File uploaded successfully!');
    }

    /**
     * Download file
     */
    public function download(TaskAttachment $attachment)
    {
        if (!Storage::disk('public')->exists($attachment->file_path)) {
            abort(404, 'File not found on server');
        }

        return Storage::disk('public')->download($attachment->file_path, $attachment->file_name);
    }

    /**
     * Delete file
     */
    public function destroy(TaskAttachment $attachment)
    {
        // Check if user is uploader or admin
        if ($attachment->uploaded_by !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }
        
        // Check if project is archived
        if ($attachment->card->board->project->is_archived) {
            return redirect()->back()->with('error', 'Cannot delete files from archived project.');
        }

        $attachment->delete(); // File will be deleted automatically via model boot

        return redirect()->back()->with('success', 'File deleted successfully!');
    }
}
