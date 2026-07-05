<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;

use App\Models\Project;
use App\Models\ProjectDelivery;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        // Anchor month from ?month=YYYY-MM, default to the current month.
        try {
            $month = $request->filled('month')
                ? Carbon::createFromFormat('Y-m', $request->input('month'))->startOfMonth()
                : Carbon::now()->startOfMonth();
        } catch (\Exception $e) {
            $month = Carbon::now()->startOfMonth();
        }

        $monthStart = $month->copy()->startOfMonth();
        $monthEnd = $month->copy()->endOfMonth();

        // The visible grid spans whole weeks (Sun–Sat) around the month.
        $gridStart = $monthStart->copy()->startOfWeek(Carbon::SUNDAY);
        $gridEnd = $monthEnd->copy()->endOfWeek(Carbon::SATURDAY);

        // Group events by Y-m-d for fast lookup in the view.
        $events = [];

        Project::whereNotNull('due_date')
            ->whereBetween('due_date', [$gridStart->toDateString(), $gridEnd->toDateString()])
            ->whereNotIn('status', ['Cancelled'])
            ->get()
            ->each(function (Project $p) use (&$events) {
                $key = $p->due_date->toDateString();
                $events[$key][] = [
                    'type' => 'project',
                    'label' => $p->project_name,
                    'status' => $p->status,
                    'url' => route('projects.show', $p),
                    'overdue' => $p->isOverdue(),
                ];
            });

        ProjectDelivery::with('project')
            ->whereNotNull('scheduled_date')
            ->whereBetween('scheduled_date', [$gridStart->toDateString(), $gridEnd->toDateString()])
            ->get()
            ->each(function (ProjectDelivery $d) use (&$events) {
                if (!$d->project) {
                    return;
                }
                $key = $d->scheduled_date->toDateString();
                $events[$key][] = [
                    'type' => 'delivery',
                    'label' => 'Deliver: ' . $d->project->project_name,
                    'status' => $d->status,
                    'url' => route('projects.show', $d->project),
                    'overdue' => false,
                ];
            });

        // Build the day cells for the grid.
        $days = [];
        for ($day = $gridStart->copy(); $day->lte($gridEnd); $day->addDay()) {
            $key = $day->toDateString();
            $days[] = [
                'date' => $day->copy(),
                'in_month' => $day->month === $monthStart->month,
                'is_today' => $day->isToday(),
                'events' => $events[$key] ?? [],
            ];
        }

        return role_view('shared.calendar.index', [
            'month' => $monthStart,
            'prevMonth' => $monthStart->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $monthStart->copy()->addMonth()->format('Y-m'),
            'days' => $days,
        ]);
    }
}
