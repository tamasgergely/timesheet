<?php

namespace App\Models;

use App\Traits\CountForCurrentUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Timer extends Model
{
    use HasFactory;
    use SoftDeletes;
    use CountForCurrentUser;
    
    public $timestamps = false;

    protected $guarded = [];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function intervals(): HasMany
    {
        return $this->hasMany(TimeInterval::class);
    }

    /**
     * Calculate the total time intervals in seconds from an array of time intervals
     * (YYYY-MM-DD HH:MM:SS - YYYY-MM-DD HH:MM:SS)
     * and convert the result to hours, minutes, and seconds.
     *
     * @param array $intervals Collection of arrays with time intervals to be summed.
     *
     * @return array An associative array representing the total time in hours, minutes, and seconds.
     */
    public function getTimerIntervalsInSeconds(Collection $intervals): array
    {
        $time['hours'] = 0;
        $time['minutes'] = 0;
        $time['seconds'] = 0;

        $seconds = 0;

        foreach ($intervals as $interval) {
            $seconds += $interval->getTimeIntervalInSeconds();
        }

        $time['hours'] = intval(floor($seconds / 3600));
        $time['minutes'] = intval(floor(($seconds - $time['hours'] * 3600) / 60));
        $time['seconds'] = intval($seconds - ($time['hours'] * 3600) - ($time['minutes'] * 60));

        return $time;
    }
}
