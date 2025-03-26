<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use App\Http\Controllers\Controller;

class PermissionController extends Controller
{
    public function index()
    {
        $this->authorize('view permissions');
        
        return Inertia::render('Permissions/Index', [
            'permissions' => Permission::orderBy('name')->get()
        ]);
    }

    public function create()
    {
        $this->authorize('create permissions');
        
        return Inertia::render('Permissions/Create');
    }

    public function store(Request $request)
    {
        $this->authorize('create permissions');
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:permissions'],
        ]);

        Permission::create($validated);

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission created successfully.');
    }

    public function show(Permission $permission)
    {
        $this->authorize('view permissions');
        
        return Inertia::render('Permissions/Show', [
            'permission' => $permission
        ]);
    }

    public function edit(Permission $permission)
    {
        $this->authorize('edit permissions');
        
        return Inertia::render('Permissions/Edit', [
            'permission' => $permission
        ]);
    }

    public function update(Request $request, Permission $permission)
    {
        $this->authorize('edit permissions');
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:permissions,name,' . $permission->id],
        ]);

        $permission->update($validated);

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission updated successfully.');
    }

    public function destroy(Permission $permission)
    {
        $this->authorize('delete permissions');

        $permission->delete();

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission deleted successfully.');
    }
}