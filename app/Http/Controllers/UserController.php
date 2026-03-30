<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $tenant = app('tenant');
        $search = $request->get('search', '');

        $users = $tenant->users()
            ->with('roles')
            ->when($search, fn($q) => $q->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            }))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Users/Index', [
            'users'  => $users,
            'search' => $search,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
        ]);

        $tenant = app('tenant');

        $user = User::create([
            'tenant_id' => $tenant->id,
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => Hash::make(\Illuminate\Support\Str::random(16)),
            'status'    => 'active',
        ]);

        $user->assignRole('member');

        return redirect()->route('users.index')
            ->with('success', "Invited {$user->name} successfully.");
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);
        $this->ensureSameTenant($user);

        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'email'  => "required|email|unique:users,email,{$user->id}",
            'status' => 'required|in:active,inactive',
        ]);

        $user->update($validated);

        return redirect()->back()->with('success', 'User updated.');
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);
        $this->ensureSameTenant($user);

        abort_if($user->id === auth()->id(), 403, 'You cannot deactivate your own account.');

        $user->update(['status' => 'inactive']);

        return redirect()->route('users.index')->with('success', "{$user->name} has been deactivated.");
    }

    public function reactivate(User $user)
    {
        $this->authorize('update', $user);
        $this->ensureSameTenant($user);

        $user->update(['status' => 'active']);

        return redirect()->route('users.index')->with('success', "{$user->name} has been reactivated.");
    }

    public function updateRole(Request $request, User $user)
    {
        $this->authorize('update', $user);
        $this->ensureSameTenant($user);

        abort_if($user->id === $request->user()->id, 403, 'You cannot change your own role.');

        $request->validate(['role' => 'required|in:admin,member']);

        $user->syncRoles([$request->role]);

        return redirect()->back()->with('success', 'Role updated.');
    }

    private function ensureSameTenant(User $user): void
    {
        $tenant = app('tenant');
        abort_if($user->tenant_id !== $tenant->id, 403, 'Access denied.');
    }
}
