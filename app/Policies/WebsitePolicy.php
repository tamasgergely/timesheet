<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use App\Models\Client;
use App\Models\Website;
use Illuminate\Support\Facades\Auth;

class WebsitePolicy
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
     * Determine whether the user can create models.
     */
    public function create(User $user, Client $client): bool
    {
        if ($user->role_id === Role::USER and $client->user_id !== $user->id) {
            
            $userTeams = Auth::user()->teams->pluck('id')->toArray();
            
            if (in_array($client->team_id, $userTeams)) {
                return true;
            }

            return false;
        }

        if ($user->role_id === Role::TEAM_LEADER and $client->user_id !== $user->id) {
            
            if (in_array($client->team_id, Auth::user()->getTeamIdsForLeader())) {
                return true;
            }

            return false;
        }
        
        return true;
    }


    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Website $website): bool
    {
        if ($user->role_id === Role::USER and $user->id !== $website->user_id) {
            return false;
        }

        if ($user->role_id === Role::TEAM_LEADER and $user->id !== $website->user_id) {
            $client = Client::find($website->client_id);

            if (!is_null($client->team) and in_array($client->team_id, Auth::user()->getTeamIdsForLeader())) {
                return true;
            }
            
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Website $website): bool
    {
        if ($user->role_id === Role::USER and ( $user->id !== $website->user_id or !is_null($website->client->team_id))) {
            return false;
        }

        if ($user->role_id === Role::TEAM_LEADER and $user->id !== $website->user_id) {
            $client = Client::find($website->client_id);

            if (!is_null($client->team) and in_array($client->team->id, Auth::user()->getTeamIdsForLeader())) {
                return true;
            }
            
            return false;
        }
 
        return true;
    }
}
