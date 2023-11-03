<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function websites()
    {
        return $this->hasMany(Website::class);
    }

    public function timers()
    {
        return $this->hasMany(Timer::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function setActiveAttribute($value)
    {
        $this->attributes['active'] = $value ? 1 : 0;
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where('name', 'like', '%' . $search . '%');
        });
    }

    public function scopeFilterByUserRole($query)
    {
        $user = Auth::user();

        // Regular user, team member
        $query->when($user->role_id === Role::USER and $user->isTeamMember, function ($query) use ($user){
            $query->where(function ($query) use ($user){
                $query->whereIn('team_id', $user->teams->pluck('id'));
                $query->orWhere('user_id', Auth::id());
            });

        // Regular user, not team member
        })->when($user->role_id === Role::USER and !$user->isTeamMember, function ($query) {
            $query->where('user_id', Auth::id());
    
        // Team leader
        })->when($user->role_id === Role::TEAM_LEADER, function ($query) {
            $query->where(function ($query) {
                $query->whereIn('team_id', Auth::user()->getTeamIdsForLeader());
                $query->orWhere('user_id', Auth::id());
            });
        });

    }
}
