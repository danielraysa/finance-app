<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine if the user can view users.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view users');
    }

    /**
     * Determine if the user can view a user.
     */
    public function view(User $user, User $model): bool
    {
        return $user->hasPermissionTo('view users');
    }

    /**
     * Determine if the user can create a user.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create user');
    }

    /**
     * Determine if the user can update a user.
     */
    public function update(User $user, User $model): bool
    {
        return $user->hasPermissionTo('edit user');
    }

    /**
     * Determine if the user can delete a user.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->hasPermissionTo('delete user');
    }
}
