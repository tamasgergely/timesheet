<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Carbon\Carbon;
use App\Models\Timer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index(Request $request)
    {   
        $timers = Timer::with(['client' => fn ($query) => $query->with('projects')])
            ->when(Auth::user()->role_id !== Role::ADMIN, function($query){
                $query->where('user_id', Auth::id());
            })
            ->whereHas('intervals', function($query) use ($request){
                $query->whereDate('start', $request->date ?? Carbon::now()->isoFormat('YYYY-MM-DD'));
            })->get();

        $timers->transform(function($timer){
            $sumIntervals = $timer->getTimerIntervalsInSeconds($timer->intervals);

            return [
                "id" => $timer->id,
                'client_name' => $timer->client->name,
                "project_name" => $timer->project->name,
                "description" => nl2br($timer->description),
                'hours' => $sumIntervals['hours'],
                'minutes' => $sumIntervals['minutes'],
                'seconds' => $sumIntervals['seconds'],
            ];
        });

        return inertia('Reports/Index', [
            'timers' => $timers
        ]);
    }
}
