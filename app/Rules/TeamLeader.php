<?php

namespace App\Rules;

use Closure;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\ValidationRule;

class TeamLeader implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (Auth::user()->role_id === Role::ADMIN and empty($value)) {
            $fail('The leader field is required.');
            return;
        }

        if (!empty($value)) {
            $leader = User::find($value);
            if (!$leader) {
                $fail('The leader is not exists.');
                return;
            }
    
            if ($leader->role_id !== Role::TEAM_LEADER) {
                $fail('The user is not a team leader.');
            }
        }
    }
}
