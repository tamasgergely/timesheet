<?php 
namespace App\Traits;

use App\Models\Role;
use Illuminate\Support\Facades\Auth;

trait CountForCurrentUser
{
    public static function getCountForCurrentUser(): Int
    {
        $user = Auth::user();
 
        if ($user->role_id === Role::USER or $user->role_id === Role::TEAM_LEADER) {
            return self::where('user_id', $user->user_id)->count();
 
        } else {
            return self::all()->count();
        }
    }
}

?>