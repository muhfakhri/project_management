<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use App\Models\ProjectMember;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share lightweight, cached role info with navbar to avoid DB on every render
        View::composer('partials.navbar', function ($view) {
            $user = Auth::user();
            if (!$user) {
                return;
            }

            $cacheKey = 'navbar_roles:' . $user->user_id;
            $roles = Cache::remember($cacheKey, now()->addSeconds(30), function () use ($user) {
                $isProjectAdmin = ProjectMember::where('user_id', $user->user_id)
                    ->where('role', 'Project Admin')
                    ->exists();
                $isTeamLead = ProjectMember::where('user_id', $user->user_id)
                    ->where('role', 'Team Lead')
                    ->exists();

                $panelName = $isProjectAdmin ? 'Admin Panel' : ($isTeamLead ? 'Team Lead Panel' : 'Project Management');
                $subtitle = $isProjectAdmin ? 'Project Management' : ($isTeamLead ? 'Project Collaboration' : 'Workspace');

                return compact('isProjectAdmin', 'isTeamLead', 'panelName', 'subtitle');
            });

            $view->with($roles);
        });
    }
}
