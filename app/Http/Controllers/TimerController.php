<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Timer;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TimerController extends Controller
{
    public function index()
    {
        $timers = Timer::with([
            'client' => function ($query) {
                $query->with(['projects' => function ($query) {
                    $query->with('website');
                }]);
            },
            'intervals' => function ($query) {
                $query->orderBy('id', 'desc');
            }
        ])->whereHas('intervals', function ($query) {
            $query->whereDate('created_at', date('Y-m-d'));
        })
        ->where('user_id', Auth::id())
        ->get();

        $timers->transform(function ($timer) {
            $sumIntervals = $timer->getTimerIntervalsInSeconds($timer->intervals);

            return [
                 "id" => $timer->id,
                 'interval_id' => $timer->intervals[0]->id,
                 'client_id' => $timer->client_id,
                 'client_name' => $timer->client->name,
                 "project_id" => $timer->project_id,
                 'projects' => $timer->client->projects,
                 "description" => $timer->description,
                 'hours' => $sumIntervals['hours'],
                 'minutes' => $sumIntervals['minutes'],
                 'seconds' => $sumIntervals['seconds'],
                 'start' => Carbon::parse($timer->intervals[0]->start)->timestamp,
                 'stop' => $timer->intervals[0]->stop ? Carbon::parse($timer->intervals[0]->stop)->timestamp : null,
                 'intervals' => $timer->intervals->slice(1)->toArray()
             ];
        });

        return inertia('Dashboard', [
            'initalTimers' => $timers,
            'clients' => $this->getClients(),
        ]);
    }

    public function store(Request $request)
    {
        $messages = [
            'client_id.required' => 'The client field is required.',
            'project_id.required' => 'The project field is required.',
            'description.required' => 'The description field is required.',
        ];

        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'project_id' => 'required|exists:projects,id',
            'description' => 'required|string'
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        $timer = Timer::create([
            'user_id' => auth()->id(),
            'client_id' => $request->client_id,
            'project_id' => $request->project_id,
            'description' => $request->description,
        ]);

        $timer->intervals()->create([
            'start' => date('Y-m-d H:i:s', $request->start),
        ]);

        return [
            'timer_id' => $timer->id,
            'interval_id' => $timer->intervals[0]->id
        ];
    }

    public function update(Request $request)
    {
        $messages = [
            'client_id.required' => 'The client field is required.',
            'project_id.required' => 'The project field is required.',
            'description.required' => 'The description field is required.',
        ];

        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'project_id' => 'required|exists:projects,id',
            'description' => 'required|string'
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        $timer = Timer::where('user_id', auth()->id())
                      ->where('id', $request->id)
                      ->first();

        if (!$timer) {
            return;
        }

        if ($request->has('time')) {
            return $this->saveTimerInterval($timer, $request);
        } else {
            $timer->client_id = $request->client_id;
            $timer->project_id = $request->project_id;
            $timer->description = $request->description;

            $timer->save();
        }
    }

    public function destroy(Timer $timer)
    {
        if ($timer->user_id === auth()->id()) {
            return $timer->delete();
        }

        return false;
    }

    private function saveTimerInterval($timer, $request)
    {
        $interval = $timer->intervals()->where('id', $request->interval_id)->first();

        // Stop
        if ($interval->stop === null) {
            $interval->stop = date('Y-m-d H:i:s', $request->time);
            $interval->save();

        // Start new interval
        } else {
            $interval = $timer->intervals()->create([
                'start' => date('Y-m-d H:i:s', $request->time),
            ]);
        }

        return [
            'timer_id' => $timer->id,
            'interval_id' => $interval->id
        ];

    }

    private function getClients()
    {
        return Client::select('id', 'name')
                ->with(['projects' => function ($query) {
                    $query->with('website');
                }])
                ->filterByUserRole()
                ->orderBy('name', 'ASC')
                ->get();
    }

}
