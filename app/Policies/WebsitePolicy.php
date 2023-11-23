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
        return $client->user_id === $user->id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Website $website): bool
    {
        return $user->id === $website->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Website $website): bool
    {
        return $user->id === $website->user_id;
    }
}
