<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Role;
use App\Models\Timer;
use App\Models\Client;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectReportController extends Controller
{
    public function index()
    {
        $timers = Timer::with(['client' => fn ($query) => $query->with('projects')])
            ->with('intervals', 'project')
            ->when(Auth::user()->role_id !== Role::ADMIN, function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->when(request('project_id'), function ($query) {
                $query->where('project_id', request('project_id'));
            })->get();
            
        $timers->transform(function ($timer) {
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

        return inertia('Reports/ProjectBased', [
            'clients' => $this->getClients(),
            'initalTimers' => $timers
        ]);
    }

    private function getClients()
    {
        return Client::select('id', 'name')
                ->with(['projects' => function ($query) {
                    $query->with('website');
                }])
                ->filterByUserRole()
                ->get();
    }
}
