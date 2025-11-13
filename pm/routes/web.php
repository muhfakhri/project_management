<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;

// Landing Page
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return view('landing');
})->name('landing');

// Login/Logout routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Registration
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// Password reset (email)
Route::get('/forgot-password', [AuthController::class, 'showForgotForm'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'reset'])->name('password.update');

// Socialite - Google login
Route::get('/auth/google/redirect', [AuthController::class, 'redirectToGoogle'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/statistics', [App\Http\Controllers\StatisticsController::class, 'index'])->name('statistics.index');
    
    // Profile Routes
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.updatePassword');
    Route::get('/profile/{user}', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    
    // Notification Routes
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [App\Http\Controllers\NotificationController::class, 'unreadCount'])->name('notifications.unreadCount');
    Route::get('/notifications/recent', [App\Http\Controllers\NotificationController::class, 'recent'])->name('notifications.recent');
    Route::post('/notifications/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
    Route::post('/notifications/delete-all-read', [App\Http\Controllers\NotificationController::class, 'deleteAllRead'])->name('notifications.deleteAllRead');
    Route::get('/notifications/{notification}', [App\Http\Controllers\NotificationController::class, 'show'])->name('notifications.show');
    Route::post('/notifications/{notification}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::delete('/notifications/{notification}', [App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');
    
    // Project Management Routes
    Route::get('/approvals', [App\Http\Controllers\ProjectController::class, 'approvals'])->name('approvals.index');
    Route::get('/projects/archived/list', [App\Http\Controllers\ProjectController::class, 'archived'])->name('projects.archived');
    Route::resource('projects', App\Http\Controllers\ProjectController::class);
    Route::get('/projects/{project}/kanban', [App\Http\Controllers\ProjectController::class, 'kanban'])->name('projects.kanban');
    Route::post('/projects/{project}/archive', [App\Http\Controllers\ProjectController::class, 'archive'])->name('projects.archive');
    Route::post('/projects/{project}/unarchive', [App\Http\Controllers\ProjectController::class, 'unarchive'])->name('projects.unarchive');
    Route::post('/projects/{project}/members', [App\Http\Controllers\ProjectController::class, 'addMember'])->name('projects.addMember');
    Route::delete('/projects/{project}/members/{member}', [App\Http\Controllers\ProjectController::class, 'removeMember'])->name('projects.removeMember');
    Route::patch('/projects/{project}/members/{member}/role', [App\Http\Controllers\ProjectController::class, 'updateMemberRole'])->name('projects.updateMemberRole');
    
    // Project Completion Approval Routes
    Route::post('/projects/{project}/request-completion', [App\Http\Controllers\ProjectController::class, 'requestCompletion'])->name('projects.requestCompletion');
    Route::post('/projects/{project}/approve-completion', [App\Http\Controllers\ProjectController::class, 'approveCompletion'])->name('projects.approveCompletion');
    Route::post('/projects/{project}/reject-completion', [App\Http\Controllers\ProjectController::class, 'rejectCompletion'])->name('projects.rejectCompletion');
    
    // Board Management Routes
    Route::resource('boards', App\Http\Controllers\BoardController::class);
    Route::get('/boards/{board}/cards', [App\Http\Controllers\BoardController::class, 'showCards'])->name('boards.cards');
    
    // Task Management Routes (Cards)
    Route::resource('tasks', App\Http\Controllers\TaskController::class);
    Route::get('/my-tasks', [App\Http\Controllers\TaskController::class, 'myTasks'])->name('tasks.my');
    Route::post('/tasks/{task}/assign', [App\Http\Controllers\TaskController::class, 'assign'])->name('tasks.assign');
    Route::delete('/tasks/{task}/assignments/{assignment}', [App\Http\Controllers\TaskController::class, 'unassign'])->name('tasks.unassign');
    Route::patch('/tasks/{task}/status', [App\Http\Controllers\TaskController::class, 'updateStatus'])->name('tasks.updateStatus');
    Route::post('/tasks/{task}/update-board', [App\Http\Controllers\TaskController::class, 'updateBoardAjax'])->name('tasks.updateBoardAjax');
    
    // Task Time Tracking Routes
    Route::post('/tasks/{task}/start', [App\Http\Controllers\TaskController::class, 'startWork'])->name('tasks.start');
    Route::post('/tasks/{task}/pause', [App\Http\Controllers\TaskController::class, 'pauseWork'])->name('tasks.pause');
    Route::post('/tasks/{task}/resume', [App\Http\Controllers\TaskController::class, 'resumeWork'])->name('tasks.resume');
    Route::post('/tasks/{task}/complete', [App\Http\Controllers\TaskController::class, 'completeWork'])->name('tasks.complete');
    
    // Task Approval Routes (Team Lead only)
    Route::post('/tasks/{task}/approve', [App\Http\Controllers\TaskController::class, 'approveTask'])->name('tasks.approve');
    Route::post('/tasks/{task}/reject', [App\Http\Controllers\TaskController::class, 'rejectTask'])->name('tasks.reject');
    
    // Subtask Routes
    Route::post('/subtasks', [App\Http\Controllers\SubtaskController::class, 'store'])->name('subtasks.store');
    Route::patch('/subtasks/{subtask}/toggle', [App\Http\Controllers\SubtaskController::class, 'toggle'])->name('subtasks.toggle');
    Route::delete('/subtasks/{subtask}', [App\Http\Controllers\SubtaskController::class, 'destroy'])->name('subtasks.destroy');
    Route::post('/subtasks/{subtask}/approve', [App\Http\Controllers\SubtaskController::class, 'approve'])->name('subtasks.approve');
    Route::post('/subtasks/{subtask}/reject', [App\Http\Controllers\SubtaskController::class, 'reject'])->name('subtasks.reject');
    
    // Task Comments Routes
    Route::post('/cards/{card}/comments', [App\Http\Controllers\TaskCommentController::class, 'store'])->name('task-comments.store');
    Route::delete('/task-comments/{comment}', [App\Http\Controllers\TaskCommentController::class, 'destroy'])->name('task-comments.destroy');
    
    // Task Attachments Routes
    Route::post('/cards/{card}/attachments', [App\Http\Controllers\TaskAttachmentController::class, 'store'])->name('task-attachments.store');
    Route::get('/attachments/{attachment}/download', [App\Http\Controllers\TaskAttachmentController::class, 'download'])->name('task-attachments.download');
    Route::delete('/task-attachments/{attachment}', [App\Http\Controllers\TaskAttachmentController::class, 'destroy'])->name('task-attachments.destroy');
    
    // Blocker Routes - Help Request System
    Route::get('/blockers', [App\Http\Controllers\BlockerController::class, 'index'])->name('blockers.index');
    Route::get('/blockers/create', [App\Http\Controllers\BlockerController::class, 'create'])->name('blockers.create');
    Route::post('/blockers', [App\Http\Controllers\BlockerController::class, 'store'])->name('blockers.store');
    Route::get('/blockers/{blocker}', [App\Http\Controllers\BlockerController::class, 'show'])->name('blockers.show');
    Route::post('/blockers/{blocker}/assign', [App\Http\Controllers\BlockerController::class, 'assign'])->name('blockers.assign');
    Route::patch('/blockers/{blocker}/status', [App\Http\Controllers\BlockerController::class, 'updateStatus'])->name('blockers.updateStatus');
    Route::post('/blockers/{blocker}/comments', [App\Http\Controllers\BlockerController::class, 'addComment'])->name('blockers.addComment');
    
    // Approval Management Routes (Project Admin & Team Lead)
    // NOTE: approvals route is handled by ProjectController::approvals to include project completion approvals
    
    // Team Management Routes
    Route::get('/team', [App\Http\Controllers\TeamController::class, 'index'])->name('team.index');
    Route::get('/team/assign', [App\Http\Controllers\TeamController::class, 'assign'])->name('team.assign');
    Route::post('/team/assign', [App\Http\Controllers\TeamController::class, 'storeAssignment'])->name('team.store-assignment');
    Route::get('/teams', [App\Http\Controllers\TeamController::class, 'index'])->name('teams.index');
    Route::get('/teams/{project}', [App\Http\Controllers\TeamController::class, 'show'])->name('teams.show');
    Route::post('/teams/{project}/members', [App\Http\Controllers\TeamController::class, 'addMember'])->name('teams.addMember');
    Route::patch('/teams/{project}/members/{member}/role', [App\Http\Controllers\TeamController::class, 'updateRole'])->name('teams.updateRole');
    Route::delete('/teams/{project}/members/{member}', [App\Http\Controllers\TeamController::class, 'removeMember'])->name('teams.removeMember');
    Route::delete('/teams/{project}/leave', [App\Http\Controllers\TeamController::class, 'leave'])->name('teams.leave');
    Route::get('/teams/member/{user}', [App\Http\Controllers\TeamController::class, 'memberProjects'])->name('teams.memberProjects');
    
    // Legacy Time Tracking routes removed (replaced by time-logs.*)
    // Route::get('/time', [App\Http\Controllers\TimeController::class, 'index'])->name('time.index');
    // Route::get('/time/reports', [App\Http\Controllers\TimeController::class, 'reports'])->name('time.reports');
    // Route::get('/time/create', [App\Http\Controllers\TimeController::class, 'create'])->name('time.create');
    // Route::post('/time', [App\Http\Controllers\TimeController::class, 'store'])->name('time.store');
    // Route::get('/time/project/{project}', [App\Http\Controllers\TimeController::class, 'project'])->name('time.project');
    // Route::get('/time/task/{card}', [App\Http\Controllers\TimeController::class, 'task'])->name('time.task');
    // Route::get('/time/{timeLog}', [App\Http\Controllers\TimeController::class, 'show'])->name('time.show');
    // Route::get('/time/{timeLog}/edit', [App\Http\Controllers\TimeController::class, 'edit'])->name('time.edit');
    // Route::put('/time/{timeLog}', [App\Http\Controllers\TimeController::class, 'update'])->name('time.update');
    // Route::delete('/time/{timeLog}', [App\Http\Controllers\TimeController::class, 'destroy'])->name('time.destroy');
    
    // Comments Routes
    Route::resource('comments', App\Http\Controllers\CommentController::class);
    Route::get('/comments/card/{card}', [App\Http\Controllers\CommentController::class, 'card'])->name('comments.card');
    Route::get('/my-comments', [App\Http\Controllers\CommentController::class, 'myComments'])->name('comments.myComments');
    Route::get('/comments/search', [App\Http\Controllers\CommentController::class, 'search'])->name('comments.search');
    
    // Time Logs Routes
    Route::get('/time-logs', [App\Http\Controllers\TimeLogController::class, 'index'])->name('time-logs.index');
    Route::get('/time-logs/my-logs', [App\Http\Controllers\TimeLogController::class, 'myLogs'])->name('time-logs.my-logs');
    Route::get('/time-logs/task/{card}', [App\Http\Controllers\TimeLogController::class, 'task'])->name('time-logs.task');
    Route::get('/time-logs/export', [App\Http\Controllers\TimeLogController::class, 'export'])->name('time-logs.export');
    
    // Statistics Routes
    Route::get('/statistics', [App\Http\Controllers\StatisticsController::class, 'index'])->name('statistics.index');
    Route::get('/statistics/reports', [App\Http\Controllers\StatisticsController::class, 'reports'])->name('statistics.reports');
    Route::get('/statistics/project/{project}', [App\Http\Controllers\StatisticsController::class, 'project'])->name('statistics.project');
    Route::get('/statistics/team', [App\Http\Controllers\StatisticsController::class, 'team'])->name('statistics.team');
    
    // API Routes for AJAX calls
    Route::get('/api/boards/{board}/members', function($boardId) {
        $board = App\Models\Board::with('project.members.user')->findOrFail($boardId);
        return response()->json($board->project->members);
    });
    
    Route::get('/api/projects/{project}/members', function($projectId) {
        $project = App\Models\Project::with(['members.user'])->findOrFail($projectId);
        
        // Filter out Project Admin and Team Lead members, add active task info for each member
        $membersWithTaskInfo = $project->members
            ->filter(function($member) use ($project) {
                // Exclude the project creator (Project Admin)
                if ($member->user_id === $project->created_by) {
                    return false;
                }
                
                // Exclude Team Lead role in project (role = 'Team Lead')
                if ($member->role === 'Team Lead') {
                    return false;
                }
                
                // Exclude Project Admin role in project (role = 'Project Admin')
                if ($member->role === 'Project Admin') {
                    return false;
                }
                
                return true;
            })
            ->map(function($member) {
                // Check if user is Project Admin (can handle multiple tasks)
                $isProjectAdmin = App\Models\ProjectMember::where('user_id', $member->user_id)
                    ->where('role', 'Project Admin')
                    ->exists();

                $activeTask = App\Models\CardAssignment::where('user_id', $member->user_id)
                    ->whereHas('card', function($q) {
                        $q->whereIn('status', ['todo', 'in_progress']);
                    })
                    ->with('card')
                    ->first();
                
                $memberData = $member->toArray();
                $memberData['is_project_admin'] = $isProjectAdmin;
                $memberData['has_active_task'] = $activeTask ? true : false;
                $memberData['active_task_title'] = $activeTask ? $activeTask->card->card_title : null;
                $memberData['can_multitask'] = $isProjectAdmin; // Project Admins can handle multiple tasks
                
                return $memberData;
            })
            ->values(); // Re-index array after filter
        
        return response()->json($membersWithTaskInfo);
    });
    
    // User Management Routes - Only for Project Admin
    Route::middleware('project-admin')->group(function () {
        Route::resource('users', App\Http\Controllers\UserController::class);
        
        // Reports Routes - Only for Project Admin
        Route::get('/reports', [App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export/projects', [App\Http\Controllers\ReportController::class, 'exportProjects'])->name('reports.export.projects');
        Route::get('/reports/export/tasks', [App\Http\Controllers\ReportController::class, 'exportTasks'])->name('reports.export.tasks');
        Route::get('/reports/export/users', [App\Http\Controllers\ReportController::class, 'exportUsers'])->name('reports.export.users');
        Route::get('/reports/export/time-logs', [App\Http\Controllers\ReportController::class, 'exportTimeLogs'])->name('reports.export.time-logs');
        Route::get('/reports/export/project-performance', [App\Http\Controllers\ReportController::class, 'exportProjectPerformance'])->name('reports.export.project-performance');
        Route::get('/reports/export/user-performance', [App\Http\Controllers\ReportController::class, 'exportUserPerformance'])->name('reports.export.user-performance');
    });
});
