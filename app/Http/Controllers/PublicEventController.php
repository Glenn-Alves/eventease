<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use App\Support\EventFilters;

class PublicEventController extends Controller
{
    public function index(Request $request)
{
    $type = $request->query('type');
    $type = (is_string($type) && isset(Event::TYPES[$type])) ? $type : null;

    $filters = EventFilters::fromRequest($request);

    $upcoming = EventFilters::apply(Event::approved()->whereDate('event_date', '>=', today()), $filters);

    // The numbers in the menu follow the search and the dates, but not the chosen type
    $counts = (clone $upcoming)
        ->selectRaw('type, count(*) as total')
        ->groupBy('type')
        ->pluck('total', 'type');

    $events = $upcoming
        ->withCount('registrations')
        ->when($type, fn ($query) => $query->where('type', $type))
        ->orderBy('event_date')
        ->orderBy('event_time')
        ->get();

    return view('public.events.index', compact('events', 'type', 'counts', 'filters'));
}

    public function show(Event $event)
    {
        $user = auth()->user();

        // Events that are not approved can only be seen by their organizer and by admins
            abort_unless(
                $event->isApproved() || ($user && ($user->isAdmin() || $user->id === $event->user_id)),
                404
            );

        // Participants use their own event page, which knows if they already registered
        if ($user && $user->role === 'participant') {
            return redirect()->route('participant.events.show', $event);
        }

        $event->loadCount('registrations');

        return view('public.events.show', compact('event'));
    }
}