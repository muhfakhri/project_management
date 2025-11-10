<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();
        
        // Get all projects where user is a member
        $projects = Project::whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->user_id);
        })->with(['members.user', 'creator'])->paginate(10);

        return view('teams.index', compact('projects'));
    }

    public function show(Project $project)
    {
        // Check if user is member of this project
        if (!$project->isMember(auth()->id())) {
            abort(403, 'This action is unauthorized.');
        }
        
        $project->load(['members.user', 'creator', 'boards.cards']);
        
        // Get team statistics
        $teamStats = [
            'total_members' => $project->members->count(),
            'project_admins' => $project->members->where('role', 'Project Admin')->count(),
            'team_leads' => $project->members->where('role', 'Team Lead')->count(),
            'developers' => $project->members->where('role', 'Developer')->count(),
            'designers' => $project->members->where('role', 'Designer')->count(),
        ];

        // Get available users to add (users not already in project)
        $availableUsers = User::whereNotIn('user_id', $project->members->pluck('user_id'))
            ->where('user_id', '!=', $project->created_by)
            ->get();

        return view('teams.show', compact('project', 'teamStats', 'availableUsers'));
    }

    public function addMember(Request $request, Project $project)
    {
        if (!$project->canManageTeam(auth()->id())) {
            abort(403, 'This action is unauthorized.');
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

        ProjectMember::create([
            'project_id' => $project->project_id,
            'user_id' => $request->user_id,
            'role' => $request->role
        ]);

        return back()->with('success', 'Team member added successfully.');
    }

    public function updateRole(Request $request, Project $project, ProjectMember $member)
    {
        if (!$project->canManageTeam(auth()->id())) {
            abort(403, 'This action is unauthorized.');
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

    public function removeMember(Project $project, ProjectMember $member)
    {
        if (!$project->canManageTeam(auth()->id())) {
            abort(403, 'This action is unauthorized.');
        }
        
        // Don't allow removing project creator
        if ($member->user_id === $project->created_by) {
            return back()->withErrors(['member' => 'Cannot remove project creator.']);
        }

        // Don't allow removing yourself
        if ($member->user_id === auth()->id()) {
            return back()->withErrors(['member' => 'Cannot remove yourself from the project.']);
        }

        $member->delete();

        return back()->with('success', 'Team member removed successfully.');
    }

    public function leave(Project $project)
    {
        $member = ProjectMember::where('project_id', $project->project_id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$member) {
            return back()->withErrors(['member' => 'You are not a member of this project.']);
        }

        // Don't allow creator to leave
        if ($member->user_id === $project->created_by) {
            return back()->withErrors(['member' => 'Project creator cannot leave the project.']);
        }

        $member->delete();

        return redirect()->route('teams.index')->with('success', 'You have left the project successfully.');
    }

    public function memberProjects($userId)
    {
        $user = User::findOrFail($userId);
        
        // Only allow viewing own projects or if user is admin
        if (auth()->id() !== $userId && !auth()->user()->isProjectAdmin()) {
            abort(403);
        }

        $projects = Project::whereHas('members', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->with(['members.user', 'creator'])->paginate(10);

        return view('teams.member-projects', compact('user', 'projects'));
    }

    public function assign()
    {
        $user = auth()->user();
        
        // Check if user has permission to assign members (is admin or team lead in any project)
        $hasPermission = Project::whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->user_id)
                  ->whereIn('role', ['Project Admin', 'Team Lead']);
        })->orWhere('created_by', $user->user_id)->exists();
        
        if (!$hasPermission) {
            abort(403, 'You do not have permission to assign team members.');
        }
        
        // Get projects where user is admin or team lead
        $projects = Project::whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->user_id)
                  ->whereIn('role', ['Project Admin', 'Team Lead']);
        })->orWhere('created_by', $user->user_id)->with(['members.user', 'creator'])->get();

        // Get all users for assignment
        $users = User::where('user_id', '!=', $user->user_id)->get();

        return view('teams.assign', compact('projects', 'users'));
    }

    public function storeAssignment(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,project_id',
            'user_id' => 'required|exists:users,user_id',
            'role' => 'required|in:Team Lead,Developer,Designer'
        ]);

        $project = Project::findOrFail($request->project_id);
        
        // Check if user has permission to assign members
        if (!$project->canManageTeam(auth()->id())) {
            abort(403, 'This action is unauthorized.');
        }

        // Check if user is already a member of THIS project
        $existingMember = ProjectMember::where('project_id', $project->project_id)
            ->where('user_id', $request->user_id)
            ->first();

        if ($existingMember) {
            return redirect()->back()->with('error', 'User is already a member of this project.');
        }

        // Check if user is already in ANY active (non-archived) project
        $activeProjectMember = ProjectMember::where('user_id', $request->user_id)
            ->whereHas('project', function($query) {
                $query->where('is_archived', false);
            })
            ->first();

        if ($activeProjectMember) {
            $activeProject = Project::find($activeProjectMember->project_id);
            return redirect()->back()->with('error', 'User is already a member of active project: ' . $activeProject->project_name . '. Users can only be assigned to one active project at a time.');
        }

        // Check if trying to assign as Team Lead
        if ($request->role === 'Team Lead') {
            // Check if THIS project already has a Team Lead
            $projectHasTeamLead = ProjectMember::where('project_id', $project->project_id)
                ->where('role', 'Team Lead')
                ->exists();

            if ($projectHasTeamLead) {
                return redirect()->back()->with('error', 'This project already has a Team Lead. Only one Team Lead is allowed per project.');
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
                return redirect()->back()->with('error', 'This user is already a Team Lead in project: ' . $existingProject->project_name . '. A Team Lead can only be assigned to one active project.');
            }
        }

        // Determine role: System admin or user with 'admin' role should become Project Admin
        $assigningUser = auth()->user();
        $userToAssign = User::find($request->user_id);
        
        $finalRole = $request->role;
        
        // If the user being assigned is a system admin, force role to Project Admin
        if ($userToAssign->role === 'admin') {
            $finalRole = 'Project Admin';
        }

        // Add member to project
        ProjectMember::create([
            'project_id' => $project->project_id,
            'user_id' => $request->user_id,
            'role' => $finalRole,
            'joined_at' => now()
        ]);

        return redirect()->route('team.assign')->with('success', 'Member assigned successfully.');
    }
}
