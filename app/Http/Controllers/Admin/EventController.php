<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Support\EventFiles;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search'));

        $events = Event::with('organizer')
            ->withCount('registrations')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('title', 'like', "%{$search}%")
                        ->orWhereHas('organizer', fn ($organizer) => $organizer
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('event_date')
            ->paginate(15)
            ->withQueryString();

        return view('admin.events.index', compact('events', 'search'));
    }

    public function destroy(Event $event)
    {
        $event->load('galleryItems');
        EventFiles::delete($event);

        $title = $event->title;
        $event->delete(); // registrations, form, categories, and gallery rows go with it

        return back()->with('status', "Event \"{$title}\" deleted.");
    }
}