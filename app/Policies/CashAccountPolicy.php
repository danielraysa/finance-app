<?php

namespace App\Policies;

use App\Models\CashAccount;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CashAccountPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CashAccount $cashAccount): bool
    {
        return $user->id === $cashAccount->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CashAccount $cashAccount): bool
    {
        return $user->id === $cashAccount->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CashAccount $cashAccount): bool
    {
        return $user->id === $cashAccount->user_id;
    }
}
