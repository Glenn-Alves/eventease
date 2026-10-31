<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class PublicEventController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type');
        $type = ($type && isset(Event::TYPES[$type])) ? $type : null;

        $upcoming = Event::whereDate('event_date', '>=', today());

        $counts = (clone $upcoming)
            ->selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $events = $upcoming
            ->withCount('registrations')
            ->when($type, fn ($query) => $query->where('type', $type))
            ->orderBy('event_date')
            ->get();

        return view('public.events.index', compact('events', 'type', 'counts'));
    }

    public function show(Event $event)
    {
        $user = auth()->user();

        // Participants use their own event page, which knows if they already registered
        if ($user && $user->role === 'participant') {
            return redirect()->route('participant.events.show', $event);
        }

        $event->loadCount('registrations');

        return view('public.events.show', compact('event'));
    }
}