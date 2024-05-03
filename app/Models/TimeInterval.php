<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeInterval extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function timer(): BelongsTo{
        return $this->belongsTo(Timer::class);
    }

    public function getTimeAttribute()
    {
        $now = Carbon::now();
        $start = Carbon::parse($this->start);

        if ($this->stop) {
            $stop = Carbon::parse($this->stop);
            $seconds = $stop->diffInSeconds($start);
        } else {
            $seconds = $now->diffInSeconds($start);
        }

        return CarbonInterval::seconds($seconds)->cascade();
    }

    /**
     * Calculate and return the time interval in seconds between 
     * the start time and end time (if available) for the current instance.
     *
     * @return int The time interval in seconds.
     */
    public function getTimeIntervalInSeconds(): Int
    {
        $now = Carbon::now();
        $start = Carbon::parse($this->start);

        if ($this->stop) {
            $stop = Carbon::parse($this->stop);
            $seconds = $stop->diffInSeconds($start);
        } else {
            $seconds = $now->diffInSeconds($start);
        }

        return $seconds;
    }
}
