<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;

class ClientPolicy
{
    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->isAdmin) {
            return true;
        }
 
        return null;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Client $client): bool
    {
        if ($user->role_id === Role::USER and $user->id !== $client->user_id) {
            return false;
        }

        if ($user->role_id === Role::TEAM_LEADER and $user->id !== $client->user_id and !in_array($client->team_id, Auth::user()->getTeamIdsForLeader())) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Client $client): bool
    {
        if ($user->role_id === Role::USER and ($user->id !== $client->user_id or !is_null($client->team_id)) ) {
            return false;
        }

        if ($user->role_id === Role::TEAM_LEADER and $user->id !== $client->user_id and !in_array($client->team_id, Auth::user()->getTeamIdsForLeader())) {
            return false;
        }
 
        return true;
    }
}
