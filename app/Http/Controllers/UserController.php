<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view users');
        
        $query = User::with('roles')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->sort, function ($query, $sort) {
                $query->orderBy($sort, $request->direction ?? 'asc');
            }, function ($query) {
                $query->orderBy('name');
            });

        return Inertia::render('Users/Index', [
            'users' => $query->paginate($request->input('per_page', 10))
                ->withQueryString(),
            'filters' => $request->only(['search', 'sort', 'direction', 'per_page'])
        ]);
    }

    public function create()
    {
        $this->authorize('create users');
        
        return Inertia::render('Users/Create', [
            'roles' => Role::orderBy('name')->get()
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create users');
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', Password::defaults()],
            'roles' => ['required', 'array'],
            'roles.*' => ['exists:roles,id'],
            'bio' => ['nullable', 'string'],
            'website' => ['nullable', 'url'],
            'social_media_links' => ['nullable', 'array']
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'bio' => $validated['bio'] ?? null,
            'website' => $validated['website'] ?? null,
            'social_media_links' => $validated['social_media_links'] ?? [],
        ]);

        $user->syncRoles($validated['roles']);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        $this->authorize('view users');
        
        return Inertia::render('Users/Show', [
            'user' => $user->load('roles.permissions'),
        ]);
    }

    public function edit(User $user)
    {
        $this->authorize('edit users');
        
        return Inertia::render('Users/Edit', [
            'user' => $user->load('roles'),
            'roles' => Role::orderBy('name')->get()
        ]);
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('edit users');
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', Password::defaults()],
            'roles' => ['required', 'array'],
            'roles.*' => ['exists:roles,id'],
            'bio' => ['nullable', 'string'],
            'website' => ['nullable', 'url'],
            'social_media_links' => ['nullable', 'array']
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'bio' => $validated['bio'] ?? null,
            'website' => $validated['website'] ?? null,
            'social_media_links' => $validated['social_media_links'] ?? [],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);
        $user->syncRoles($validated['roles']);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $this->authorize('delete users');

        if ($user->hasRole('super-admin')) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Super Admin user cannot be deleted.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}