<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view roles');
        
        $query = Role::with('permissions')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->sort, function ($query, $sort) {
                $query->orderBy($sort, $request->direction ?? 'asc');
            }, function ($query) {
                $query->orderBy('name');
            });

        return Inertia::render('Roles/Index', [
            'roles' => $query->paginate($request->input('per_page', 10))
                ->withQueryString(),
            'filters' => $request->only(['search', 'sort', 'direction', 'per_page'])
        ]);
    }

    public function create()
    {
        $this->authorize('create roles');
        
        return Inertia::render('Roles/Create', [
            'permissions' => Permission::orderBy('name')->get()
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create roles');
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles'],
            'permissions' => ['required', 'array'],
            'permissions.*' => ['exists:permissions,id']
        ]);

        $role = Role::create([
            'name' => $validated['name']
        ]);

        $role->syncPermissions($validated['permissions']);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role created successfully.');
    }

    public function show(Role $role)
    {
        $this->authorize('view roles');
        
        return Inertia::render('Roles/Show', [
            'role' => $role->load('permissions')
        ]);
    }

    public function edit(Role $role)
    {
        $this->authorize('edit roles');
        
        if($role->name === 'super-admin') {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Super Admin role cannot be edited.');
        }
        
        return Inertia::render('Roles/Edit', [
            'role' => $role->load('permissions'),
            'permissions' => Permission::orderBy('name')->get()
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $this->authorize('edit roles');
        
        if($role->name === 'super-admin') {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Super Admin role cannot be edited.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,' . $role->id],
            'permissions' => ['required', 'array'],
            'permissions.*' => ['exists:permissions,id']
        ]);

        $role->update([
            'name' => $validated['name']
        ]);

        $role->syncPermissions($validated['permissions']);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        $this->authorize('delete roles');

        if($role->name === 'super-admin') {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Super Admin role cannot be deleted.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role deleted successfully.');
    }
}