<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Registration;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    private const PERIODS = [7, 30, 90];

    public function index(Request $request)
    {
        $events = $this->summary();
        $selected = $this->selectedEvent($request, $events);
        $days = $this->days($request);

        // The numbers follow the chosen event, or all of the organizer's events
        $scope = $selected ? collect([$selected]) : $events;
        $ids = $scope->pluck('id');

        $totalRegistrations = (int) $scope->sum('registrations_count');
        $checkedIn = (int) $scope->sum('checked_in_count');
        $revenue = (float) $scope->sum('revenue');

        // Slots only exist for events that use the EventEase form
        $withSlots = $scope->where('registration_mode', 'builtin');
        $slots = (int) $withSlots->sum('total_slots');
        $registeredInSlots = (int) $withSlots->sum('registrations_count');

        $attendanceRate = $totalRegistrations > 0 ? (int) round($checkedIn / $totalRegistrations * 100) : null;
        $fillRate = $slots > 0 ? (int) round($registeredInSlots / $slots * 100) : null;

        // Registrations per day, with zeros for days nobody registered
        $start = today()->subDays($days - 1);

        $perDay = Registration::whereIn('event_id', $ids)
            ->where('created_at', '>=', $start->copy()->startOfDay())
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $series = collect(CarbonPeriod::create($start, today()))->map(fn ($date) => [
            'label' => $date->format('M d'),
            'value' => (int) ($perDay[$date->format('Y-m-d')] ?? 0),
        ])->values();

        $newInPeriod = (int) $series->sum('value');

        $byCategory = Registration::whereIn('registrations.event_id', $ids)
            ->join('event_categories', 'registrations.event_category_id', '=', 'event_categories.id')
            ->groupBy('event_categories.name')
            ->selectRaw('event_categories.name as name, COUNT(*) as total')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => ['label' => $row->name, 'value' => (int) $row->total])
            ->values();

        $byType = $scope->groupBy('type')
            ->map(fn ($group, $type) => [
                'label' => Event::TYPES[$type] ?? 'Others',
                'value' => (int) $group->sum('registrations_count'),
            ])
            ->sortByDesc('value')
            ->values();

        return view('organizer.reports.index', compact(
            'events', 'selected', 'days', 'totalRegistrations', 'checkedIn', 'revenue',
            'slots', 'registeredInSlots', 'attendanceRate', 'fillRate',
            'series', 'newInPeriod', 'byCategory', 'byType'
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        $events = $this->summary();
        $selected = $this->selectedEvent($request, $events);
        $rows = $selected ? collect([$selected]) : $events;

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');

            // Byte order mark, so Excel reads special characters correctly
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'Event', 'Type', 'Date', 'Time', 'Form', 'Total slots', 'Registered',
                'Slots filled (%)', 'Checked in', 'Attendance (%)', 'Expected revenue (PHP)',
            ], ',', '"', '');

            foreach ($rows as $event) {
                $external = $event->registration_mode === 'external';
                $registered = $event->registrations_count;

                fputcsv($out, [
                    $this->safeCell($event->title),
                    Event::TYPES[$event->type] ?? 'Others',
                    $event->event_date->format('Y-m-d'),
                    substr($event->event_time, 0, 5),
                    $external ? 'External form' : 'EventEase form',
                    $external ? '' : $event->total_slots,
                    $external ? '' : $registered,
                    ($external || $event->total_slots < 1) ? '' : round($registered / $event->total_slots * 100),
                    $external ? '' : $event->checked_in_count,
                    ($external || $registered < 1) ? '' : round($event->checked_in_count / $registered * 100),
                    number_format($event->revenue, 2, '.', ''),
                ], ',', '"', '');
            }

            fclose($out);
        }, 'eventease-report-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // All of the organizer's events, with their counts
    private function summary()
    {
        $events = Event::where('user_id', auth()->id())
            ->withCount([
                'registrations',
                'registrations as checked_in_count' => fn ($query) => $query->whereNotNull('checked_in_at'),
            ])
            ->orderByDesc('event_date')
            ->orderByDesc('event_time')
            ->get();

        $revenue = Registration::join('event_categories', 'registrations.event_category_id', '=', 'event_categories.id')
            ->whereIn('registrations.event_id', $events->pluck('id'))
            ->groupBy('registrations.event_id')
            ->selectRaw('registrations.event_id as event_id, SUM(event_categories.price) as revenue')
            ->pluck('revenue', 'event_id');

        foreach ($events as $event) {
            $event->revenue = (float) ($revenue[$event->id] ?? 0);
        }

        return $events;
    }

    // Only one of the organizer's own events can be chosen
    private function selectedEvent(Request $request, $events)
    {
        $id = (int) $request->query('event');

        return $id ? $events->firstWhere('id', $id) : null;
    }

    private function days(Request $request): int
    {
        $days = (int) $request->query('days', 30);

        return in_array($days, self::PERIODS, true) ? $days : 30;
    }

    // A cell that starts with = + - @ can run as a formula when the file opens in Excel
    private function safeCell(?string $value): string
    {
        $value = (string) $value;

        if ($value === '' || is_numeric($value)) {
            return $value;
        }

        return preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }
}