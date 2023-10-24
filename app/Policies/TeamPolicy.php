<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return ($user->role_id === Role::ADMIN or $user->role_id === Role::TEAM_LEADER);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return ($user->role_id === Role::ADMIN or $user->role_id === Role::TEAM_LEADER);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Team $team): bool
    {
        return ($user->role_id === Role::ADMIN or ($user->role_id === Role::TEAM_LEADER and $user->id === $team->leader_id));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Team $team): bool
    {
        return ($user->role_id === Role::ADMIN or ($user->role_id === Role::TEAM_LEADER and $user->id === $team->leader_id));
    }
}
