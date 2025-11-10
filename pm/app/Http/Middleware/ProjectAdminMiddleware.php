<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;

class ProjectAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        $user = auth()->user();

        // Check if user is system admin OR has Project Admin role in any project
        $isAdmin = $user->role === 'admin';
        $isProjectAdmin = DB::table('project_members')
            ->where('user_id', $user->user_id)
            ->where('role', 'Project Admin')
            ->exists();

        if (!$isAdmin && !$isProjectAdmin) {
            abort(403, 'Access denied. Only Admin or Project Admin can access this feature.');
        }

        return $next($request);
    }
}
