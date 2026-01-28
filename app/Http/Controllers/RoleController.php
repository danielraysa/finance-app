<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    public function index(Request $request)
    {
        $query = Role::with('permissions');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

        // Sorting
        $sortBy = $request->query('sort_by', 'created_at');
        $sortDir = $request->query('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $roles = $query->paginate(15)->appends($request->query());

        return Inertia::render('Roles/Index', [
            'roles' => $roles,
            'filters' => $request->query(),
        ]);
    }

    public function create()
    {
        $permissions = Permission::all();

        return Inertia::render('Roles/Create', [
            'permissions' => $permissions,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string',
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        // Assign permissions
        $permissions = Permission::whereIn('id', $validated['permissions'])->pluck('name')->toArray();
        $role->syncPermissions($permissions);

        return redirect()->route('roles.index')
            ->with('success', 'Role created successfully.');
    }

    public function show(Role $role)
    {
        $role->load('permissions');

        return Inertia::render('Roles/Show', [
            'role' => $role,
        ]);
    }

    public function edit(Role $role)
    {
        $role->load('permissions');
        $permissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return Inertia::render('Roles/Edit', [
            'role' => $role,
            'permissions' => $permissions,
            'rolePermissions' => $rolePermissions,
        ]);
    }

    public function update(Request $request, Role $role)
    {
        // Prevent editing of protected roles
        if (in_array($role->name, ['admin', 'user', 'verificator'])) {
            return redirect()->back()->with('error', 'This role cannot be modified.');
        }

        $validated = $request->validate([
            'name' => "required|string|max:255|unique:roles,name,{$role->id}",
            'description' => 'nullable|string',
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        // Sync permissions
        $permissions = Permission::whereIn('id', $validated['permissions'])->pluck('name')->toArray();
        $role->syncPermissions($permissions);

        return redirect()->route('roles.index')
            ->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        // Prevent deleting of protected roles
        if (in_array($role->name, ['admin', 'user', 'verificator'])) {
            return redirect()->back()->with('error', 'This role cannot be deleted.');
        }

        // Check if role is assigned to any users
        if ($role->users()->exists()) {
            return redirect()->back()->with('error', 'This role is assigned to users and cannot be deleted.');
        }

        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', 'Role deleted successfully.');
    }
}
