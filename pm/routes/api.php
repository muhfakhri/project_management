<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\BlockerController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public routes (no authentication required)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile/update', [AuthController::class, 'updateProfile']);
    
    // Task routes
    Route::get('/tasks/my', [TaskController::class, 'myTasks']);
    Route::get('/tasks/history', [TaskController::class, 'history']);
    Route::get('/tasks/{id}', [TaskController::class, 'show']);
    Route::post('/tasks/{id}/status', [TaskController::class, 'updateStatus']);
    Route::post('/tasks/{id}/start', [TaskController::class, 'startWork']);
    Route::post('/tasks/{id}/pause', [TaskController::class, 'pauseWork']);
    Route::post('/tasks/{id}/complete', [TaskController::class, 'completeWork']);
    
    // Dashboard routes
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    
    // Notification routes
    Route::get('/notifications/recent', [NotificationController::class, 'recent']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    
    // Blocker routes - Help Request System
    Route::get('/blockers', [BlockerController::class, 'index']);
    Route::get('/blockers/my', [BlockerController::class, 'myBlockers']);
    Route::get('/blockers/assigned', [BlockerController::class, 'assignedToMe']);
    Route::get('/blockers/{id}', [BlockerController::class, 'show']);
    Route::post('/blockers', [BlockerController::class, 'store']);
    Route::post('/blockers/{id}/assign', [BlockerController::class, 'assign']);
    Route::patch('/blockers/{id}/status', [BlockerController::class, 'updateStatus']);
    Route::post('/blockers/{id}/comments', [BlockerController::class, 'addComment']);
});
