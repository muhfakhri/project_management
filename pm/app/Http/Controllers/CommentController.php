<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Comment;
use App\Models\Notification;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();
        
        // Get comments from cards in projects user is member of
        $comments = Comment::whereHas('card.board.project.members', function ($query) use ($user) {
            $query->where('user_id', $user->user_id);
        })
        ->with(['card.board.project', 'user'])
        ->orderBy('created_at', 'desc')
        ->paginate(15);

        return view('comments.index', compact('comments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'card_id' => 'required|exists:cards,card_id',
            'content' => 'required|string|max:1000'
        ]);

        $card = Card::with('board.project')->findOrFail($request->card_id);
        
        // Prevent commenting on locked (approved & done) tasks
        if ($card->isLocked()) {
            return back()->with('error', 'This task has been approved and is locked. Comments cannot be added.');
        }
        
        // Check if project is archived
        if ($card->board->project->isArchived()) {
            return back()->with('error', 'Cannot add comments to tasks in an archived project.');
        }
        
        // Check if user has access to this card's project
        $project = $card->board->project;
        if (!$project->members->contains('user_id', auth()->id()) && auth()->user()->role !== 'Project Admin') {
            abort(403, 'You do not have access to comment on this task.');
        }

        Comment::create([
            'card_id' => $request->card_id,
            'user_id' => auth()->id(),
            'comment_text' => $request->content
        ]);

        // Send notification to task assignees (except commenter)
        $assignees = $card->assignments->pluck('user_id')->filter(function ($userId) {
            return $userId !== auth()->id();
        });

        foreach ($assignees as $userId) {
            Notification::create_notification(
                $userId,
                'comment_added',
                'New Comment on Task',
                auth()->user()->full_name . ' commented on: ' . $card->card_title,
                [
                    'task_id' => $card->card_id,
                    'task_title' => $card->card_title,
                    'comment_preview' => substr($request->content, 0, 100),
                    'commented_by' => auth()->id()
                ],
                route('tasks.show', $card)
            );
        }

        // Also notify task creator if they're not the commenter
        if ($card->created_by !== auth()->id() && !$assignees->contains($card->created_by)) {
            Notification::create_notification(
                $card->created_by,
                'comment_added',
                'New Comment on Your Task',
                auth()->user()->full_name . ' commented on: ' . $card->card_title,
                [
                    'task_id' => $card->card_id,
                    'task_title' => $card->card_title,
                    'comment_preview' => substr($request->content, 0, 100),
                    'commented_by' => auth()->id()
                ],
                route('tasks.show', $card)
            );
        }

        return back()->with('success', 'Comment added successfully.');
    }

    public function show(Comment $comment)
    {
        // Check if user has access to this comment's project
        $project = $comment->card->board->project;
        if (!$project->members->contains('user_id', auth()->id()) && auth()->user()->role !== 'Project Admin') {
            abort(403);
        }

        $comment->load(['card.board.project', 'user']);

        return view('comments.show', compact('comment'));
    }

    public function edit(Comment $comment)
    {
        // Only allow editing own comments
        if ($comment->user_id !== auth()->id()) {
            abort(403);
        }

        $comment->load(['card.board.project']);

        return view('comments.edit', compact('comment'));
    }

    public function update(Request $request, Comment $comment)
    {
        // Only allow updating own comments
        if ($comment->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'content' => 'required|string|max:1000'
        ]);

        $comment->update([
            'comment_text' => $request->content
        ]);

        return redirect()->route('tasks.show', $comment->card->card_id)->with('success', 'Comment updated successfully.');
    }

    public function destroy(Comment $comment)
    {
        // Load card relationship
        $comment->load('card.board.project');

        // Check if project is archived
        if ($comment->card->board->project->isArchived()) {
            return back()->with('error', 'Cannot delete comments in archived projects.');
        }

        // Only allow deleting own comments or if user is project admin
        if ($comment->user_id !== auth()->id() && auth()->user()->role !== 'Project Admin') {
            abort(403);
        }

        $card = $comment->card;
        $comment->delete();

        return back()->with('success', 'Comment deleted successfully.');
    }

    public function card(Card $card)
    {
        // Check if user has access to this card's project
        $project = $card->board->project;
        if (!$project->members->contains('user_id', auth()->id()) && auth()->user()->role !== 'Project Admin') {
            abort(403);
        }

        $card->load(['board.project']);
        
        $comments = Comment::where('card_id', $card->card_id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('comments.card', compact('card', 'comments'));
    }

    public function myComments()
    {
        $user = auth()->user();
        
        $comments = Comment::where('user_id', $user->user_id)
            ->with(['card.board.project'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('comments.my-comments', compact('comments'));
    }

    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:3'
        ]);

        $user = auth()->user();
        $query = $request->query;

        $comments = Comment::whereHas('card.board.project.members', function ($q) use ($user) {
            $q->where('user_id', $user->user_id);
        })
        ->where('content', 'like', "%{$query}%")
        ->with(['card.board.project', 'user'])
        ->orderBy('created_at', 'desc')
        ->paginate(15);

        return view('comments.search', compact('comments', 'query'));
    }
}
