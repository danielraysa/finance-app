<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin')->except(['index', 'show']);
    }

    public function index(Request $request)
    {
        $query = User::with('roles');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Role filter
        if ($request->filled('role')) {
            $role = $request->query('role');
            $query->whereHas('roles', function ($q) use ($role) {
                $q->where('name', $role);
            });
        }

        // Sorting
        $sortBy = $request->query('sort_by', 'created_at');
        $sortDir = $request->query('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $users = $query->paginate(15)->appends($request->query());
        $roles = Role::all();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'roles' => $roles,
            'filters' => $request->query(),
        ]);
    }

    public function create()
    {
        $this->authorize('create', User::class);
        $roles = Role::all();

        return Inertia::render('Users/Create', [
            'roles' => $roles,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Assign roles
        $roles = Role::whereIn('id', $validated['roles'])->pluck('name')->toArray();
        $user->assignRole($roles);

        $creator = $request->user();
        activity()->performedOn($user)->log($creator->name . ' membuat user baru: ' . $user->name);
        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        $user->load('roles');

        return Inertia::render('Users/Show', [
            'user' => $user,
            'roles' => $user->roles,
        ]);
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);
        $user->load('roles');
        $roles = Role::all();

        return Inertia::render('Users/Edit', [
            'user' => $user,
            'roles' => $roles,
            'userRoles' => $user->roles->pluck('id')->toArray(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|email|unique:users,email,{$user->id}",
            'password' => 'nullable|min:8|confirmed',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($validated['password'] ?? null) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        // Sync roles
        $roles = Role::whereIn('id', $validated['roles'])->pluck('name')->toArray();
        $user->syncRoles($roles);

        $creator = $request->user();
        activity()->performedOn($user)->log($creator->name . ' melakukan perubahan data user: ' . $user->name);
        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        // Prevent deleting yourself
        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        $creator = Auth::user();
        activity()->performedOn($user)->log($creator->name . ' menghapus user: ' . $user->name);
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }
}
