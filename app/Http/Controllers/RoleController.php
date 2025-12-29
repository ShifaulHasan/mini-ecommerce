<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();

        return view('role-permission.roles', compact('roles', 'permissions'));
    }

    public function assignPermissions(Request $request, Role $role)
    {
        $role->syncPermissions($request->permissions ?? []);

        return back()->with('success', 'Permissions updated successfully');
    }
}
