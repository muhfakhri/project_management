<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::paginate(10);
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:users|max:50',
            'full_name' => 'required|max:100',
            'email' => 'required|email|unique:users|max:100',
            'password' => 'required|min:6',
            'current_task_status' => 'required|in:idle,working'
        ]);

        User::create([
            'username' => $request->username,
            'full_name' => $request->full_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'current_task_status' => $request->current_task_status
        ]);

        return redirect()->route('users.index')->with('success', 'User berhasil dibuat!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::findOrFail($id);
        
        // Get user's project memberships
        $projectMemberships = DB::table('project_members')
            ->join('projects', 'project_members.project_id', '=', 'projects.project_id')
            ->where('project_members.user_id', $id)
            ->select('projects.project_name', 'project_members.role', 'project_members.joined_at')
            ->get();

        return view('users.show', compact('user', 'projectMemberships'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'username' => 'required|max:50|unique:users,username,' . $id . ',user_id',
            'full_name' => 'required|max:100',
            'email' => 'required|email|max:100|unique:users,email,' . $id . ',user_id',
            'current_task_status' => 'required|in:idle,working'
        ]);

        $updateData = [
            'username' => $request->username,
            'full_name' => $request->full_name,
            'email' => $request->email,
            'current_task_status' => $request->current_task_status
        ];

        // Only update password if provided
        if ($request->filled('password')) {
            $request->validate(['password' => 'min:6']);
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return redirect()->route('users.index')->with('success', 'User berhasil diupdate!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        
        // Don't allow deleting current logged in user
        if ($user->user_id == auth()->id()) {
            return redirect()->route('users.index')->with('error', 'Anda tidak bisa menghapus akun sendiri!');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'User berhasil dihapus!');
    }
}
