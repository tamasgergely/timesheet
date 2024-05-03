<?php

namespace App\Services;

use App\Models\Client;
use Carbon\Carbon;
use App\Models\Timer;
use App\Models\Project;
use App\Models\TimeInterval;
use Illuminate\Database\Eloquent\Collection;

class ReportService
{
    public function getTopClientsByWorkMinutes()
    {
        return Client::selectRaw('clients.*, SUM(TIMESTAMPDIFF(SECOND, time_intervals.start, time_intervals.stop)) as total_seconds')
            ->join('timers', 'clients.id', '=', 'timers.client_id')
            ->join('time_intervals', 'timers.id', '=', 'time_intervals.timer_id')
            ->groupBy('clients.id')
            ->orderByDesc('total_seconds')
            ->limit(5)
            ->get();
    }


    public function getTopProjectsByWorkMinutes()
    {
        // $projectsTimers = Timer::with('intervals')->get()->groupBy('project_id');

        // $sumAll = 0;
        // foreach ($projectsTimers as $key => $timers) {
        //     $sum = 0;
        //     foreach ($timers as $timer) {
        //         $sum += $this->calculateTotalSeconds($timer->intervals);
        //     }

        //     $projectsTimes[$key] = $sum;
        //     $sumAll += $sum;
        // }

        // $projectsTimes = collect($projectsTimes)->sortDesc()->chunk(5)[0];

        // $projects = Project::whereIn('id', $projectsTimes->keys())
        //     ->orderByRaw("FIELD(id, " . implode(',', $projectsTimes->keys()->toArray()) . ")")
        //     ->get();

        return Project::selectRaw('projects.*, SUM(TIMESTAMPDIFF(SECOND, time_intervals.start, time_intervals.stop)) as total_seconds')
            ->join('timers', 'projects.id', '=', 'timers.project_id')
            ->join('time_intervals', 'timers.id', '=', 'time_intervals.timer_id')
            ->groupBy('projects.id')
            ->orderByDesc('total_seconds')
            ->limit(5)
            ->get();
    }


    /**
     * Calculates the total worked minutes for each month based on stored time intervals.
     *
     * @param int $months The number of months to consider for the calculation (default: 12).
     * @return array Associative array where keys are formatted dates (Y-m) and values are worked minutes for each month.
     */
    public function calculateMonthlyWorkedMinutes(int $months = 12): array
    {
        $monthlyTimeIntervals = $this->fetchMonthlyTimeIntervals($months);

        $workedMinutesPerMonths = [];

        $monthlyTimeIntervals->each(function ($timeIntervals, $date) use (&$workedMinutesPerMonths) {
            $sumInSeconds = $this->calculateTotalSeconds($timeIntervals);
            $workedMinutesPerMonths[$date] = $this->convertSecondsToMinutes($sumInSeconds);
        });

        return $workedMinutesPerMonths;
    }

    /**
     * Fetches monthly time intervals from the database and groups them by month.
     *
     * @param int $months The number of months for which to fetch time intervals.
     * @return \Illuminate\Support\Collection Collection of time intervals grouped by month.
     */
    private function fetchMonthlyTimeIntervals(int $months): Collection
    {
        return TimeInterval::orderBy('time_intervals.start', 'asc')
            ->whereDate('start', '>=', Carbon::today()->subMonth($months)->toDateString())
            ->get()
            ->groupBy(function ($interval) {
                return Carbon::parse($interval->start)->format('Y-m');
            });
    }

    /**
     * Calculates the total seconds worked based on an array of time intervals.
     *
     * @param \Illuminate\Support\Collection $timeIntervals Collection of time intervals.
     * @return int Total seconds worked.
     */
    private function calculateTotalSeconds(Collection $timeIntervals): int
    {
        return $timeIntervals->reduce(function ($carry, $timeInterval) {
            return $carry + $timeInterval->getTimeIntervalInSeconds();
        }, 0);
    }

    /**
     * Converts total seconds to minutes with one decimal place rounding.
     *
     * @param int $seconds Total seconds to be converted.
     * @return float Worked minutes with one decimal place.
     */
    private function convertSecondsToMinutes(int $seconds): float
    {
        return round($seconds / 60, 1);
    }
}
