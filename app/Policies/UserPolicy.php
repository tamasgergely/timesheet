<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role_id === Role::ADMIN;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role_id === Role::ADMIN;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $currentUser, User $user): bool
    {
        return ($currentUser->role_id === Role::ADMIN or $currentUser->id === $user->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $currentUser, User $user): bool
    {
        return $currentUser->role_id === Role::ADMIN or $currentUser->id === $user->id;
    }
}
