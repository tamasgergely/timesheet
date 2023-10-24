<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    public const ADMIN = 1;

    public const TEAM_LEADER = 2;

    public const USER = 3;

    public static $roleNames = [
        self::ADMIN => 'Admin',
        self::TEAM_LEADER => 'Team Leader',
        self::USER => 'User',
    ];

}
