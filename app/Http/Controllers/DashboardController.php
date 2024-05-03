<?php

namespace App\Http\Controllers;

use App\Models\Timer;
use App\Models\Client;
use App\Models\Project;
use App\Models\Website;
use App\Services\ReportService;

class DashboardController extends Controller
{
    public function index(ReportService $reportService)
    {
        return inertia('Dashboard/Index', [
            'timersCount' => Timer::getCountForCurrentUser(),
            'clientsCount' => Client::getCountForCurrentUser(),
            'projectsCount' => Project::getCountForCurrentUser(),
            'websitesCount' => Website::getCountForCurrentUser(),
            'workedMinutesPerMonths' => $reportService->calculateMonthlyWorkedMinutes(),
            'topProjects' => $topProjects = $reportService->getTopProjectsByWorkMinutes(),
            'topProjectsTotalSeconds' => $topProjects->sum('total_seconds'),
            'topClients' => $topClients = $reportService->getTopClientsByWorkMinutes(),
            'topClientsTotalSeconds' => $topClients->sum('total_seconds'),
        ]);
    }
}
