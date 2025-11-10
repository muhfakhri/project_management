<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Show the form for editing the user's profile
     */
    public function edit()
    {
        $user = auth()->user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Update the user's profile information
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'username' => 'required|string|max:50|unique:users,username,' . $user->user_id . ',user_id',
            'email' => 'required|email|max:100|unique:users,email,' . $user->user_id . ',user_id',
            'full_name' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:500',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $data = $request->only(['username', 'email', 'full_name', 'phone', 'bio']);

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if exists
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            $path = $request->file('profile_picture')->store('profile-pictures', 'public');
            $data['profile_picture'] = $path;
        }

        $user->update($data);

        return redirect()->route('profile.edit')->with('success', 'Profile updated successfully!');
    }

    /**
     * Update the user's password
     */
    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'current_password' => 'required',
            'new_password' => ['required', 'confirmed', Password::min(8)],
        ]);

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return redirect()->route('profile.edit')->with('success', 'Password updated successfully!');
    }

    /**
     * Show the user's public profile
     */
    public function show($userId)
    {
        $user = \App\Models\User::findOrFail($userId);
        
        // Get user's projects
        $projects = \App\Models\Project::whereHas('members', function($query) use ($userId) {
            $query->where('user_id', $userId);
        })->with('creator')->get();

        // Get user's tasks
        $tasks = \App\Models\Card::whereHas('assignments', function($query) use ($userId) {
            $query->where('user_id', $userId);
        })->with('board.project')->latest()->take(10)->get();

        // Get stats
        $stats = [
            'total_projects' => $projects->count(),
            'total_tasks' => \App\Models\CardAssignment::where('user_id', $userId)->count(),
            'completed_tasks' => \App\Models\Card::whereHas('assignments', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })->where('status', 'done')->count(),
            'time_logged' => \App\Models\TimeLog::where('user_id', $userId)->sum('duration_minutes') / 60
        ];

        return view('profile.show', compact('user', 'projects', 'tasks', 'stats'));
    }
}
