<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeInterval extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function getTimeAttribute()
    {
        $now = Carbon::now();
        $start = Carbon::parse($this->start);

        if ($this->stop){
            $stop = Carbon::parse($this->stop);
            $seconds = $stop->diffInSeconds($start);
        }else{
            $seconds = $now->diffInSeconds($start);
        }

        return CarbonInterval::seconds($seconds)->cascade();
    }

    public function getTimeIntervalInSeconds()
    {
        $now = Carbon::now();
        $start = Carbon::parse($this->start);

        if ($this->stop){
            $stop = Carbon::parse($this->stop);
            $seconds = $stop->diffInSeconds($start);
        }else{
            $seconds = $now->diffInSeconds($start);
        }

        return $seconds;
    }
}
