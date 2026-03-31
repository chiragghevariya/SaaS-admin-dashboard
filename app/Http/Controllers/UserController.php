<?php

namespace App\Http\Controllers;

use App\Mail\UserDeactivated;
use App\Mail\UserInvited;
use App\Mail\UserReactivated;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
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

        $tenant = app('tenant');

        // Require an active subscription before inviting anyone
        if (! $tenant->subscribed('default')) {
            return redirect()->route('billing.plans')
                ->with('error', 'You need an active subscription to invite team members. Please choose a plan first.');
        }

        // Enforce plan user limit
        $limit = $tenant->userLimit();
        if ($limit !== null && $limit > 0 && $tenant->users()->count() >= $limit) {
            return back()->with('error', "Your {$tenant->activePlan()} plan allows up to {$limit} users. Upgrade your plan to invite more members.");
        }

        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
        ]);

        $user = User::create([
            'tenant_id'         => $tenant->id,
            'name'              => $validated['name'],
            'email'             => $validated['email'],
            'password'          => Hash::make(\Illuminate\Support\Str::random(16)),
            'status'            => 'active',
            'email_verified_at' => now(),
        ]);

        $user->assignRole('member');

        $token = Password::broker()->createToken($user);
        $setPasswordUrl = route('password.reset', ['token' => $token, 'email' => $user->email]);
        Mail::to($user->email)->send(new UserInvited($user, $setPasswordUrl));

        return redirect()->route('users.index')
            ->with('success', "Invited {$user->name} successfully.");
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);
        $this->ensureSameTenant($user);

        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => "required|email|unique:users,email,{$user->id}",
        ]);

        $user->update($validated);

        return redirect()->back()->with('success', 'User updated.');
    }

    /**
     * Deactivate a user (status → inactive, user remains visible in the list).
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);
        $this->ensureSameTenant($user);

        abort_if($user->id === auth()->id(), 403, 'You cannot deactivate your own account.');

        $user->update(['status' => 'inactive']);
        Mail::to($user->email)->send(new UserDeactivated($user));

        return redirect()->route('users.index')
            ->with('success', "{$user->name} has been deactivated.");
    }

    /**
     * Permanently delete a user via soft-delete (removed from all lists).
     * Only available for inactive users to prevent accidental deletion.
     */
    public function forceDestroy(User $user)
    {
        $this->authorize('delete', $user);
        $this->ensureSameTenant($user);

        abort_if($user->id === auth()->id(), 403, 'You cannot delete your own account.');
        abort_if($user->status === 'active', 422, 'Deactivate the user before deleting them.');

        $name = $user->name;
        $user->delete(); // soft-delete — excluded from all queries automatically

        return redirect()->route('users.index')
            ->with('success', "{$name} has been permanently deleted.");
    }

    public function reactivate(User $user)
    {
        $this->authorize('update', $user);
        $this->ensureSameTenant($user);

        $user->update(['status' => 'active']);
        Mail::to($user->email)->send(new UserReactivated($user));

        return redirect()->route('users.index')
            ->with('success', "{$user->name} has been reactivated.");
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
