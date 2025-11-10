<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get active (non-archived) projects where user is a member
        $projects = Project::where('is_archived', false)
            ->whereHas('members', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->with(['creator', 'members.user'])
            ->paginate(10);

        return view('projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Only users who are Project Admin in at least one project can create new projects
        if (!auth()->user()->canCreateProject()) {
            return redirect()->route('projects.index')->with('error', 'Only Project Admins can create new projects. Please contact an administrator.');
        }

        return view('projects.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Only users who are Project Admin in at least one project can create new projects
        if (!auth()->user()->canCreateProject()) {
            return redirect()->route('projects.index')->with('error', 'Only Project Admins can create new projects. Please contact an administrator.');
        }

        $request->validate([
            'project_name' => 'required|max:100',
            'description' => 'nullable',
            'status' => 'nullable|in:planning,in_progress,done,on_hold',
            'start_date' => 'nullable|date',
            'deadline' => 'nullable|date'
        ]);

        // Check if user is already in any active project
        $activeProjectMember = ProjectMember::where('user_id', auth()->id())
            ->whereHas('project', function($query) {
                $query->where('is_archived', false);
            })
            ->first();

        if ($activeProjectMember) {
            $activeProject = Project::find($activeProjectMember->project_id);
            return redirect()->route('projects.index')->with('error', 'You are already a member of active project: ' . $activeProject->project_name . '. Please complete or archive that project before creating a new one.');
        }

        $project = Project::create([
            'project_name' => $request->project_name,
            'description' => $request->description,
            'status' => $request->status ?? 'planning',
            'created_by' => auth()->id(),
            'start_date' => $request->start_date,
            'deadline' => $request->deadline
        ]);

        // Add creator as Project Admin
        ProjectMember::create([
            'project_id' => $project->project_id,
            'user_id' => auth()->id(),
            'role' => 'Project Admin'
        ]);

        return redirect()->route('projects.index')->with('success', 'Project created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $project = Project::with(['creator', 'members.user', 'boards.cards'])->findOrFail($id);
        
        // Check if user is member of this project
        if (!$project->members->contains('user_id', auth()->id())) {
            abort(403, 'Access denied to this project.');
        }

        // Get available users to add (users not already in project)
        $availableUsers = \App\Models\User::whereNotIn('user_id', $project->members->pluck('user_id'))
            ->where('user_id', '!=', $project->created_by)
            ->get();

        return view('projects.show', compact('project', 'availableUsers'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $project = Project::with(['members.user'])->findOrFail($id);
        
        // Check if user is admin of this project
        if (!$project->isAdmin(auth()->id())) {
            abort(403, 'Only Project Admin can edit this project.');
        }

        // Get available users to add (users not already in project)
        $availableUsers = \App\Models\User::whereNotIn('user_id', $project->members->pluck('user_id'))
            ->where('user_id', '!=', $project->created_by)
            ->get();

        return view('projects.edit', compact('project', 'availableUsers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $project = Project::findOrFail($id);
        
        // Check if user is admin of this project
        if (!$project->isAdmin(auth()->id())) {
            abort(403, 'Only Project Admin can update this project.');
        }

        $request->validate([
            'project_name' => 'required|max:100',
            'description' => 'nullable',
            'status' => 'nullable|in:planning,in_progress,done,on_hold',
            'start_date' => 'nullable|date',
            'deadline' => 'nullable|date'
        ]);

        // Check if status is being changed to 'done'
        $oldStatus = $project->status;
        $newStatus = $request->input('status', $oldStatus);
        
        $project->update($request->only(['project_name', 'description', 'status', 'start_date', 'deadline']));

        // Auto-archive when status changed to 'done'
        if ($oldStatus !== 'done' && $newStatus === 'done') {
            $project->update([
                'is_archived' => true,
                'archived_at' => now(),
                'archived_by' => auth()->id()
            ]);
            
            // Auto-remove all members from the project when archived
            $removedMembers = ProjectMember::where('project_id', $project->project_id)->get();
            
            foreach ($removedMembers as $member) {
                // Notify each member that they have been removed due to project completion
                \App\Models\Notification::create([
                    'user_id' => $member->user_id,
                    'type' => 'project_archived',
                    'title' => 'Project Completed',
                    'message' => 'You have been removed from project "' . $project->project_name . '" as it has been completed and archived.',
                    'url' => route('projects.show', $project->project_id),
                    'read' => false
                ]);
            }
            
            // Delete all project members
            ProjectMember::where('project_id', $project->project_id)->delete();
            
            return redirect()->route('projects.archived')->with('success', 'Project marked as complete and archived successfully! All members have been removed and can now join new projects.');
        }

        return redirect()->route('projects.index')->with('success', 'Project updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $project = Project::findOrFail($id);
        
        // Check if user is admin of this project
        if (!$project->isAdmin(auth()->id())) {
            abort(403, 'Only Project Admin can delete this project.');
        }

        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Project deleted successfully!');
    }

    /**
     * Archive a project (mark as completed and archived)
     */
    public function archive(Project $project)
    {
        // Check if user is admin of this project
        if (!$project->isAdmin(auth()->id())) {
            abort(403, 'Only Project Admin can archive this project.');
        }

        $project->update([
            'status' => 'done',
            'is_archived' => true,
            'archived_at' => now(),
            'archived_by' => auth()->id()
        ]);

        // Auto-remove all members from the project when archived
        // This allows them to join new projects
        $removedMembers = ProjectMember::where('project_id', $project->project_id)->get();
        
        foreach ($removedMembers as $member) {
            // Notify each member that they have been removed due to project completion
            \App\Models\Notification::create([
                'user_id' => $member->user_id,
                'type' => 'project_archived',
                'title' => 'Project Completed',
                'message' => 'You have been removed from project "' . $project->project_name . '" as it has been completed and archived.',
                'url' => route('projects.show', $project->project_id),
                'read' => false
            ]);
        }
        
        // Delete all project members
        ProjectMember::where('project_id', $project->project_id)->delete();

        return redirect()->route('projects.show', $project->project_id)
            ->with('success', 'Project archived successfully! All members have been removed and can now join new projects.');
    }

    /**
     * Unarchive a project
     */
    public function unarchive(Project $project)
    {
        // Check if user is admin of this project
        if (!$project->isAdmin(auth()->id())) {
            abort(403, 'Only Project Admin can unarchive this project.');
        }

        // Note: When unarchiving, members are NOT automatically re-added
        // because they may now be in other active projects
        // Project Admin will need to manually re-assign members

        $project->update([
            'status' => 'in_progress', // Reset to in_progress when unarchived
            'is_archived' => false,
            'archived_at' => null,
            'archived_by' => null
        ]);

        $message = 'Project unarchived successfully! Status changed to In Progress. Please note: Members were not automatically re-added. You will need to manually assign team members again.';

        return redirect()->route('projects.show', $project->project_id)
            ->with('success', $message);
    }

    /**
     * Show archived projects
     */
    public function archived()
    {
        $user = auth()->user();
        
        if ($user->role === 'admin') {
            // Admin can see all archived projects
            $projects = Project::where('is_archived', true)
                ->with('creator')
                ->orderBy('archived_at', 'desc')
                ->paginate(12);
        } else {
            // Regular users see archived projects they're members of
            $projects = Project::where('is_archived', true)
                ->where(function($query) use ($user) {
                    $query->where('created_by', $user->user_id)
                        ->orWhereHas('members', function($q) use ($user) {
                            $q->where('user_id', $user->user_id);
                        });
                })
                ->with('creator')
                ->orderBy('archived_at', 'desc')
                ->paginate(12);
        }

        return view('projects.archived', compact('projects'));
    }

    public function addMember(Request $request, Project $project)
    {
        // Check if project is archived
        if ($project->isArchived()) {
            return back()->with('error', 'Cannot add members to an archived project.');
        }
        
        // Check if user is admin of this project
        if (!$project->isAdmin(auth()->id())) {
            abort(403, 'Only Project Admin can add members.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,user_id',
            'role' => 'required|in:Team Lead,Developer,Designer'
        ]);

        // Check if user is already a member of THIS project
        $existingMember = ProjectMember::where('project_id', $project->project_id)
            ->where('user_id', $request->user_id)
            ->first();

        if ($existingMember) {
            return back()->withErrors(['user_id' => 'User is already a member of this project.']);
        }

        // Check if user is already in ANY active (non-archived) project
        $activeProjectMember = ProjectMember::where('user_id', $request->user_id)
            ->whereHas('project', function($query) {
                $query->where('is_archived', false);
            })
            ->first();

        if ($activeProjectMember) {
            $activeProject = Project::find($activeProjectMember->project_id);
            return back()->withErrors(['user_id' => 'User is already a member of active project: ' . $activeProject->project_name . '. Users can only be assigned to one active project at a time.']);
        }

        // Check if trying to assign as Team Lead
        if ($request->role === 'Team Lead') {
            // Check if THIS project already has a Team Lead
            $projectHasTeamLead = ProjectMember::where('project_id', $project->project_id)
                ->where('role', 'Team Lead')
                ->exists();

            if ($projectHasTeamLead) {
                return back()->withErrors([
                    'role' => 'This project already has a Team Lead. Only one Team Lead is allowed per project.'
                ]);
            }

            // Check if user is already a Team Lead in another ACTIVE (non-archived) project
            $existingTeamLead = ProjectMember::where('user_id', $request->user_id)
                ->where('role', 'Team Lead')
                ->whereHas('project', function($query) {
                    $query->where('is_archived', false);
                })
                ->first();

            if ($existingTeamLead) {
                $existingProject = Project::find($existingTeamLead->project_id);
                return back()->withErrors([
                    'role' => 'This user is already a Team Lead in project: ' . $existingProject->project_name . '. A Team Lead can only be assigned to one active project.'
                ]);
            }
        }

        // Determine role: System admin should become Project Admin
        $userToAssign = User::find($request->user_id);
        $finalRole = $request->role;
        
        // If the user being assigned is a system admin, force role to Project Admin
        if ($userToAssign->role === 'admin') {
            $finalRole = 'Project Admin';
        }

        ProjectMember::create([
            'project_id' => $project->project_id,
            'user_id' => $request->user_id,
            'role' => $finalRole
        ]);

        // Send notification to the new member
        Notification::create_notification(
            $request->user_id,
            'project_invitation',
            'Added to Project',
            'You have been added to project: ' . $project->project_name . ' as ' . $request->role,
            [
                'project_id' => $project->project_id,
                'project_name' => $project->project_name,
                'role' => $request->role,
                'added_by' => auth()->id()
            ],
            route('projects.show', $project)
        );

        return back()->with('success', 'Team member added successfully.');
    }

    public function removeMember(Project $project, ProjectMember $member)
    {
        // Check if project is archived
        if ($project->isArchived()) {
            return back()->with('error', 'Cannot remove members from an archived project.');
        }
        
        // Check if user is admin of this project
        if (!$project->isAdmin(auth()->id())) {
            abort(403, 'Only Project Admin can remove members.');
        }

        // Don't allow removing project creator
        if ($member->user_id === $project->created_by) {
            return back()->withErrors(['member' => 'Cannot remove project creator.']);
        }

        $member->delete();

        return back()->with('success', 'Team member removed successfully.');
    }

    public function updateMemberRole(Request $request, Project $project, ProjectMember $member)
    {
        // Check if project is archived
        if ($project->isArchived()) {
            return back()->with('error', 'Cannot update member roles in an archived project.');
        }
        
        // Check if user is admin of this project
        if (!$project->isAdmin(auth()->id())) {
            abort(403, 'Only Project Admin can update member roles.');
        }

        $request->validate([
            'role' => 'required|in:Team Lead,Developer,Designer'
        ]);

        // Don't allow changing role of project creator
        if ($member->user_id === $project->created_by) {
            return back()->withErrors(['role' => 'Cannot change role of project creator.']);
        }

        // Check if trying to change to Team Lead
        if ($request->role === 'Team Lead' && $member->role !== 'Team Lead') {
            // Check if THIS project already has a Team Lead
            $projectHasTeamLead = ProjectMember::where('project_id', $project->project_id)
                ->where('role', 'Team Lead')
                ->where('member_id', '!=', $member->member_id)
                ->exists();

            if ($projectHasTeamLead) {
                return back()->withErrors([
                    'role' => 'This project already has a Team Lead. Only one Team Lead is allowed per project.'
                ]);
            }

            // Check if user is already a Team Lead in another ACTIVE (non-archived) project
            $existingTeamLead = ProjectMember::where('user_id', $member->user_id)
                ->where('role', 'Team Lead')
                ->where('member_id', '!=', $member->member_id)
                ->whereHas('project', function($query) {
                    $query->where('is_archived', false);
                })
                ->first();

            if ($existingTeamLead) {
                $existingProject = Project::find($existingTeamLead->project_id);
                return back()->withErrors([
                    'role' => 'This user is already a Team Lead in project: ' . $existingProject->project_name . '. A Team Lead can only be assigned to one active project.'
                ]);
            }
        }

        $member->update(['role' => $request->role]);

        return back()->with('success', 'Member role updated successfully.');
    }

    /**
     * Show Kanban Board view for a project
     */
    public function kanban(Project $project)
    {
        // Authorization check
        if (!$project->isMember(auth()->id())) {
            return redirect()
                ->route('projects.index')
                ->with('error', 'You are not authorized to view this project.');
        }

        // Get all boards with their cards
        $boards = $project->boards()
            ->with([
                'cards' => function($query) {
                    $query->with([
                        'assignments.user',
                        'subtasks',
                        'comments'
                    ])->orderBy('position');
                }
            ])
            ->orderBy('position')
            ->get();

        return view('projects.kanban', compact('project', 'boards'));
    }
}
