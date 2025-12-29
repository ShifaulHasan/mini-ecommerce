<?php

namespace App\Http\Controllers;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;

class UserRoleController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->get();
        $roles = Role::all();

        return view('role-permission.user-roles', compact('users', 'roles'));
    }

    public function assignRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'role' => 'required'
        ]);

        $user = User::findOrFail($request->user_id);
        $user->syncRoles([$request->role]); // old role remove + new role assign

        return back()->with('success', 'Role assigned successfully');
    }
}
