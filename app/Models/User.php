<?php

namespace App\Models;

use App\Models\Client;
use App\Models\Project;
use App\Models\Website;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class)->withTimestamps();
    }

    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    public function websites()
    {
        return $this->hasMany(Website::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function timers()
    {
        return $this->hasMany(Timer::class);
    }

    public function ledTeams()
    {
        return $this->hasMany(Team::class, 'leader_id');
    }

    protected function isTeamMember(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->teams()->count() > 0
        );
    }

    protected function isAdmin(): Attribute
    {
        return Attribute::make(
            get: fn($value, array $attributes) => $attributes['role_id'] === Role::ADMIN
        );
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function($query) use ($search){
                $query->where('name', 'like', '%' . $search . '%');
                $query->orWhere('email', 'like', '%' . $search . '%');
            });
        
        })->when(Auth::user()->role_id === Role::USER or Auth::user()->role_id === Role::TEAM_LEADER, function ($query){
            $query->where('id', Auth::id());
        });
    }

    /**
     * Which teams the team leader leads
     */
    public function getTeamIdsForLeader()
    {
        return $this->ledTeams->pluck('id')->toArray();
    }
}