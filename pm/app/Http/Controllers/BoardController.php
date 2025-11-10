<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Project;
use Illuminate\Http\Request;

class BoardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();
        
        // Get boards from projects user is member of
        $boards = Board::whereHas('project.members', function ($query) use ($user) {
            $query->where('user_id', $user->user_id);
        })->with(['project', 'cards'])->paginate(10);

        return view('boards.index', compact('boards'));
    }

    public function create()
    {
        $user = auth()->user();
        
        // Get projects where user is Project Admin only
        $projects = Project::whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->user_id)
                  ->where('role', 'Project Admin');
        })->orWhere('created_by', $user->user_id)->get();

        return view('boards.create', compact('projects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,project_id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status_mapping' => 'nullable|in:todo,in_progress,review,done'
        ]);

        $project = Project::findOrFail($request->project_id);
        
        // Check if user can create boards in this project (only Project Admin)
        $userMember = $project->members->where('user_id', auth()->id())->first();
        
        // Allow if project creator
        if (!$userMember && $project->created_by !== auth()->id()) {
            return redirect()->back()->with('error', 'You do not have permission to create boards in this project.')->withInput();
        }

        // If member exists, ensure they are Project Admin only
        if ($userMember && !$userMember->isAdmin()) {
            return redirect()->back()->with('error', 'Only Project Admins can create boards. Contact your project admin for assistance.')->withInput();
        }

        Board::create([
            'project_id' => $request->project_id,
            'board_name' => $request->name,
            'description' => $request->description,
            'status_mapping' => $request->status_mapping
        ]);

        return redirect()->route('projects.show', $project)->with('success', 'Board created successfully!');
    }

    public function show(Board $board)
    {
        // Check if user has access to this board's project
        $project = $board->project;
        if (!$project->members->contains('user_id', auth()->id()) && $project->created_by !== auth()->id()) {
            abort(403);
        }

        $board->load(['project', 'cards.assignments.user', 'cards.subtasks']);

        return view('boards.show', compact('board'));
    }

    public function edit(Board $board)
    {
        $project = $board->project;
        $userMember = $project->members->where('user_id', auth()->id())->first();
        
        // Check permissions (only Project Admin)
        if (!$userMember && $project->created_by !== auth()->id()) {
            return redirect()->route('projects.show', $project)->with('error', 'Access denied. Only Project Admins can edit boards.');
        }

        if ($userMember && !$userMember->isAdmin()) {
            return redirect()->route('projects.show', $project)->with('error', 'Only Project Admins can edit boards. Contact your project admin for assistance.');
        }

        return view('boards.edit', compact('board'));
    }

    public function update(Request $request, Board $board)
    {
        $project = $board->project;
        $userMember = $project->members->where('user_id', auth()->id())->first();
        
        // Check permissions (only Project Admin)
        if (!$userMember && $project->created_by !== auth()->id()) {
            return redirect()->route('projects.show', $project)->with('error', 'Access denied. Only Project Admins can update boards.');
        }

        if ($userMember && !$userMember->isAdmin()) {
            return redirect()->route('projects.show', $project)->with('error', 'Only Project Admins can update boards. Contact your project admin for assistance.');
        }

        $request->validate([
            'board_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'position' => 'required|integer|min:0',
            'status_mapping' => 'nullable|in:todo,in_progress,review,done'
        ]);

        $board->update([
            'board_name' => $request->board_name,
            'description' => $request->description,
            'position' => $request->position,
            'status_mapping' => $request->status_mapping
        ]);

        return redirect()->route('projects.show', $project)->with('success', 'Board "' . $board->board_name . '" updated successfully!');
    }

    public function destroy(Board $board)
    {
        $project = $board->project;
        $userMember = $project->members->where('user_id', auth()->id())->first();
        
        // Check permissions (only Project Admin)
        if (!$userMember && $project->created_by !== auth()->id()) {
            return redirect()->route('projects.show', $project)->with('error', 'Access denied. Only Project Admins can delete boards.');
        }

        if ($userMember && !$userMember->isAdmin()) {
            return redirect()->route('projects.show', $project)->with('error', 'Only Project Admins can delete boards. Contact your project admin for assistance.');
        }

        $projectId = $board->project_id;
        $boardName = $board->board_name;
        $board->delete();

        return redirect()->route('projects.show', $projectId)->with('success', "Board '{$boardName}' deleted successfully!");
    }

    public function showCards(Board $board)
    {
        // Check if user has access to this board's project
        $project = $board->project;
        if (!$project->members->contains('user_id', auth()->id()) && $project->created_by !== auth()->id()) {
            abort(403);
        }

        $board->load(['cards' => function ($query) {
            $query->with(['assignments.user', 'subtasks']);
        }]);

        return view('boards.cards', compact('board'));
    }
}
