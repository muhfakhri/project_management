<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use App\Models\Project;

class MenuServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Share menu items with both sidebar and navbar
        View::composer(['partials.sidebar', 'partials.navbar'], function ($view) {
            $menuItems = $this->getMenuItems();
            $view->with('menuItems', $menuItems);
        });
    }

    private function getMenuItems()
    {
        // Check if current user is authenticated and has admin or Project Admin role
        $isAdmin = false;
        $isProjectAdmin = false;
        if (auth()->check()) {
            $isAdmin = auth()->user()->role === 'admin';
            $isProjectAdmin = $isAdmin || auth()->user()->isProjectAdmin();
        }

        $menuItems = [
            [
                'label' => 'Dashboard',
                'icon' => 'bi bi-house',
                'route' => 'dashboard',
            ],
            [
                'label' => 'Statistics',
                'icon' => 'bi bi-graph-up',
                'route' => 'statistics.index',
            ],
        ];

        // Projects menu - conditionally include "Create Project"
        $projectsMenu = [
            'label' => 'Projects',
            'icon' => 'bi bi-folder',
            'route' => 'projects.index',
            'children' => [
                ['label' => 'All Projects', 'route' => 'projects.index'],
            ],
        ];

        // Only add "Create Project" for Admin or Project Admin
        if (auth()->check() && auth()->user()->canCreateProject()) {
            $projectsMenu['children'][] = ['label' => 'Create Project', 'route' => 'projects.create'];
        }

        $menuItems[] = $projectsMenu;

        // Tasks menu - conditionally include "Create Task"
        $tasksMenu = [
            'label' => 'Tasks',
            'icon' => 'bi bi-check-square',
            'route' => 'tasks.index',
            'children' => [
                ['label' => 'All Tasks', 'route' => 'tasks.index'],
                ['label' => 'My Tasks', 'route' => 'tasks.my'],
            ],
        ];

        // Only add "Create Task" for Admin or Team Lead
        if (auth()->check()) {
            $canManageTasks = $isAdmin || auth()->user()->projectMembers()
                ->whereIn('role', ['Project Admin', 'Team Lead'])
                ->exists();
            
            if ($canManageTasks) {
                $tasksMenu['children'][] = ['label' => 'Create Task', 'route' => 'tasks.create'];
            }
        }

        $menuItems[] = $tasksMenu;

        $menuItems = array_merge($menuItems, [
            [
                'label' => 'Team',
                'icon' => 'bi bi-people',
                'route' => 'team.index',
                'children' => [
                    ['label' => 'Team Members', 'route' => 'team.index'],
                ],
            ],
            [
                'label' => 'Blockers',
                'icon' => 'fas fa-exclamation-triangle',
                'route' => 'blockers.index',
            ],
            [
                'label' => 'Time Logs',
                'icon' => 'bi bi-clock-history',
                'route' => 'time-logs.index',
                'children' => [
                    ['label' => 'All Time Logs', 'route' => 'time-logs.index'],
                    ['label' => 'My Time Logs', 'route' => 'time-logs.my-logs'],
                ],
            ],
            [
                'label' => 'Comments',
                'icon' => 'bi bi-chat-dots',
                'route' => 'comments.index',
            ],
        ]);

        // Add Assign Members menu item for users who can manage teams
        if (auth()->check()) {
            $canAssignMembers = $isAdmin || Project::whereHas('members', function ($query) {
                $query->where('user_id', auth()->id())
                      ->whereIn('role', ['Project Admin', 'Team Lead']);
            })->orWhere('created_by', auth()->id())->exists();
            
            if ($canAssignMembers) {
                // Find the Team menu and add the assign members item
                foreach ($menuItems as &$menuItem) {
                    if ($menuItem['label'] === 'Team' && isset($menuItem['children'])) {
                        $menuItem['children'][] = ['label' => 'Assign Members', 'route' => 'team.assign'];
                        break;
                    }
                }
            }
        }

        // Add Approvals menu for Admin, Project Admin, and Team Lead
        if (auth()->check()) {
            $canApprove = $isAdmin || auth()->user()->projectMembers()
                             ->whereIn('role', ['Project Admin', 'Team Lead'])
                             ->exists();
            
            if ($canApprove) {
                // Get pending approvals count
                $pendingCount = 0;
                
                if ($isAdmin) {
                    // System Admin can see all pending approvals
                    $pendingCount = DB::table('subtasks')
                        ->where('needs_approval', true)
                        ->where('status', 'done')
                        ->where('is_approved', false)
                        ->whereNull('rejection_reason')
                        ->count();
                    
                    $pendingCount += DB::table('cards')
                        ->where('needs_approval', true)
                        ->where('status', 'review')
                        ->where('is_approved', false)
                        ->whereNull('rejection_reason')
                        ->count();
                } else {
                    // Team Lead sees only their projects
                    $projectIds = auth()->user()->projectMembers()
                        ->whereIn('role', ['Project Admin', 'Team Lead'])
                        ->pluck('project_id');
                    
                    if ($projectIds->isNotEmpty()) {
                        $pendingCount = DB::table('subtasks')
                            ->join('cards', 'subtasks.card_id', '=', 'cards.card_id')
                            ->join('boards', 'cards.board_id', '=', 'boards.board_id')
                            ->whereIn('boards.project_id', $projectIds)
                            ->where('subtasks.needs_approval', true)
                            ->where('subtasks.status', 'done')
                            ->where('subtasks.is_approved', false)
                            ->whereNull('subtasks.rejection_reason')
                            ->count();
                        
                        $pendingCount += DB::table('cards')
                            ->join('boards', 'cards.board_id', '=', 'boards.board_id')
                            ->whereIn('boards.project_id', $projectIds)
                            ->where('cards.needs_approval', true)
                            ->where('cards.status', 'review')
                            ->where('cards.is_approved', false)
                            ->whereNull('cards.rejection_reason')
                            ->count();
                    }
                }
                
                $menuItems[] = [
                    'label' => 'Approvals',
                    'icon' => 'bi bi-clipboard-check',
                    'route' => 'approvals.index',
                    'badge' => $pendingCount > 0 ? $pendingCount : null,
                    'badge_class' => 'bg-warning',
                ];
            }
        }

        // Only show User Management and Reports for Admin or Project Admin
        if ($isProjectAdmin) {
            $menuItems[] = [
                'label' => 'User Management',
                'icon' => 'bi bi-person-gear',
                'route' => 'users.index',
                'children' => [
                    ['label' => 'All Users', 'route' => 'users.index'],
                    ['label' => 'Add User', 'route' => 'users.create'],
                ],
            ];
            
            // Add Reports menu for Admin or Project Admin
            $menuItems[] = [
                'label' => 'Reports',
                'icon' => 'bi bi-file-earmark-bar-graph',
                'route' => 'reports.index',
            ];
        }

        return $menuItems;
    }
}
