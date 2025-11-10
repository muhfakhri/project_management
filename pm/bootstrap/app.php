<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'project-admin' => \App\Http\Middleware\ProjectAdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle 403 Authorization errors with user-friendly redirects
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            if ($e->getStatusCode() === 403) {
                // Get the error message
                $message = $e->getMessage() ?: 'This action is unauthorized.';
                
                // For AJAX requests, return JSON
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => $message,
                        'message' => $message,
                    ], 403);
                }
                
                // For web requests, redirect back with error message
                return redirect()->back()->with('error', $message);
            }
        });
        
        // Handle Authorization exceptions from policies
        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, $request) {
            $message = $e->getMessage() ?: 'You do not have permission to perform this action.';
            
            // For AJAX requests, return JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => $message,
                    'message' => $message,
                ], 403);
            }
            
            // For web requests, redirect back with error message
            return redirect()->back()->with('error', $message);
        });
    })->create();
