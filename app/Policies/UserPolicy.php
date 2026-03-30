<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $authUser): bool
    {
        return $authUser->hasAnyRole(['admin', 'super_admin']);
    }

    public function view(User $authUser, User $user): bool
    {
        return $authUser->hasAnyRole(['admin', 'super_admin'])
            && $authUser->tenant_id === $user->tenant_id;
    }

    public function create(User $authUser): bool
    {
        return $authUser->hasAnyRole(['admin', 'super_admin']);
    }

    public function update(User $authUser, User $user): bool
    {
        return $authUser->hasAnyRole(['admin', 'super_admin'])
            && $authUser->tenant_id === $user->tenant_id;
    }

    public function delete(User $authUser, User $user): bool
    {
        return $authUser->hasAnyRole(['admin', 'super_admin'])
            && $authUser->tenant_id === $user->tenant_id
            && $authUser->id !== $user->id; // can't deactivate yourself
    }
}
