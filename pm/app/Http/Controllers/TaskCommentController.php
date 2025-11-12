<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\TaskComment;
use App\Models\CommentMention;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;

class TaskCommentController extends Controller
{
    /**
     * Store a new comment
     */
    public function store(Request $request, $cardId)
    {
        $request->validate([
            'comment' => 'required|string'
        ]);

        $card = Card::findOrFail($cardId);
        
        // Check if user can work on this task
        if (!$card->canWorkOn(auth()->user())) {
            return redirect()->back()->with('error', 'You are not authorized to comment on this task. Only assigned members can add comments.');
        }
        
        // Check if task is locked
        if ($card->isLocked()) {
            return redirect()->back()->with('error', 'This task is locked. Comments are disabled.');
        }
        
        // Create comment
        $comment = TaskComment::create([
            'card_id' => $card->card_id,
            'user_id' => auth()->id(),
            'comment' => $request->comment
        ]);

        // Process @mentions
        $this->processMentions($comment, $card);

        return redirect()->back()->with('success', 'Comment added successfully!');
    }

    /**
     * Delete a comment
     */
    public function destroy($commentId)
    {
        $comment = TaskComment::findOrFail($commentId);
        
        // Check if user owns the comment or is admin
        if ($comment->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $comment->delete();

        return redirect()->back()->with('success', 'Comment deleted successfully!');
    }

    /**
     * Process @mentions in comment
     */
    private function processMentions(TaskComment $comment, Card $card)
    {
        $usernames = $comment->extractMentions();

        foreach ($usernames as $username) {
            $user = User::where('username', $username)->first();
            
            if ($user && $user->user_id !== auth()->id()) {
                // Create mention record
                CommentMention::create([
                    'comment_id' => $comment->id,
                    'mentioned_user_id' => $user->user_id,
                    'is_read' => false
                ]);

                // Send notification
                Notification::create([
                    'user_id' => $user->user_id,
                    'type' => 'comment_mention',
                    'title' => 'Mentioned in Comment',
                    'message' => auth()->user()->full_name . ' mentioned you in a comment on task "' . $card->card_title . '"',
                    'url' => route('tasks.show', $card->card_id) . '#comment-' . $comment->id,
                    'read' => false
                ]);
            }
        }

        // Notify task assignees (except the commenter)
        foreach ($card->assignees as $assignee) {
            if ($assignee->user_id !== auth()->id()) {
                Notification::create([
                    'user_id' => $assignee->user_id,
                    'type' => 'task_comment',
                    'title' => 'New Comment on Task',
                    'message' => auth()->user()->full_name . ' commented on task "' . $card->card_title . '"',
                    'url' => route('tasks.show', $card->card_id) . '#comment-' . $comment->id,
                    'read' => false
                ]);
            }
        }
    }
}
