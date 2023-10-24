<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

use function Symfony\Component\String\b;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Timer extends Model
{
    use HasFactory;
    use SoftDeletes;

    public $timestamps = false;

    protected $guarded = [];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function intervals(): HasMany
    {
        return $this->hasMany(TimeInterval::class);
    }

    public function getTimerIntervalsInSeconds($intervals): array
    {
        $time['hours'] = 0;
        $time['minutes'] = 0;
        $time['seconds'] = 0;

        $seconds = 0;

        foreach ($intervals as $interval){
            $seconds += $interval->getTimeIntervalInSeconds();
        }

        $time['hours'] = intval(floor($seconds / 3600));
        $time['minutes'] = intval(floor(($seconds - $time['hours'] * 3600) / 60));
        $time['seconds'] = intval($seconds - ($time['hours'] * 3600) - ($time['minutes'] * 60));

        return $time;
    }
}
