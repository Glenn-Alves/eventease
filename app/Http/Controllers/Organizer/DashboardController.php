<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Registration;

class DashboardController extends Controller
{
    public function index()
    {
        $mine = Event::where('user_id', auth()->id());

        $totalEvents = (clone $mine)->count();

        $upcoming = (clone $mine)
            ->whereDate('event_date', '>=', today())
            ->withCount('registrations')
            ->orderBy('event_date')
            ->get();

        $totalRegistrations = Registration::whereHas(
            'event',
            fn ($query) => $query->where('user_id', auth()->id())
        )->count();

        // Slots left only make sense for events that use the EventEase form
        $slotsLeft = $upcoming
            ->where('registration_mode', 'builtin')
            ->sum(fn ($event) => max($event->total_slots - $event->registrations_count, 0));

        $recent = Registration::with(['user', 'event', 'category'])
            ->whereHas('event', fn ($query) => $query->where('user_id', auth()->id()))
            ->latest()
            ->limit(8)
            ->get();

        return view('organizer.dashboard', [
            'totalEvents' => $totalEvents,
            'upcomingCount' => $upcoming->count(),
            'totalRegistrations' => $totalRegistrations,
            'slotsLeft' => $slotsLeft,
            'nextEvents' => $upcoming->take(5),
            'recent' => $recent,
        ]);
    }
}