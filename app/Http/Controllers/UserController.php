<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users with search, filter, and pagination.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search by name, email, company, or phone
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Role filter
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $users = $query->latest()->paginate($perPage)->withQueryString();

        $roles = User::ROLES;

        return view('users.index', compact('users', 'roles'));
    }

    /**
     * Show form to create a new user.
     */
    public function create()
    {
        $roles = User::ROLES;
        return view('users.create', compact('roles'));
    }

    /**
     * Store a new user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'role' => ['required', Rule::in(User::ROLES)],
            'company_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('users.index')
            ->with('success', 'User created successfully!');
    }

    /**
     * Show details of a single user.
     */
    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    /**
     * Show form to edit a user.
     */
    public function edit(User $user)
    {
        $roles = User::ROLES;
        return view('users.edit', compact('user', 'roles'));
    }

    /**
     * Update a user's information.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|min:8|confirmed',
            'role' => ['required', Rule::in(User::ROLES)],
            'company_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully!');
    }

    /**
     * Delete a user.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account!');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully!');
    }

    /**
     * Bulk delete selected users.
     */
    public function bulkDelete(Request $request)
    {
        $ids = explode(',', $request->input('user_ids', ''));
        $ids = array_filter($ids, fn($id) => $id != auth()->id()); // prevent deleting self

        if (!empty($ids)) {
            User::whereIn('id', $ids)->delete();
        }

        return redirect()->route('users.index')
            ->with('success', 'Selected users deleted successfully!');
    }

    /**
     * AJAX: Get suppliers list
     */
    public function getSuppliers()
    {
        return response()->json(
            User::where('role', 'Supplier')->get([
                'id',
                'name',
                'company_name',
                'email',
                'phone'
            ])
        );
    }

    /**
     * AJAX: Get customers list
     */
    public function getCustomers()
    {
        return response()->json(
            User::where('role', 'Customer')->get([
                'id',
                'name',
                'company_name',
                'email',
                'phone'
            ])
        );
    }
}
