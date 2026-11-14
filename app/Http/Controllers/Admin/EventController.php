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
            $status = in_array($request->query('status'), ['pending', 'approved', 'rejected'], true)
                ? $request->query('status')
                : null;

            $events = Event::with('organizer')
                ->withCount('registrations')
                ->when($status, fn ($query) => $query->where('approval_status', $status))
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($inner) use ($search) {
                        $inner->where('title', 'like', "%{$search}%")
                            ->orWhereHas('organizer', fn ($organizer) => $organizer
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%"));
                    });
                })
                ->orderByRaw("CASE approval_status WHEN 'pending' THEN 0 ELSE 1 END")
                ->orderByDesc('event_date')
                ->paginate(15)
                ->withQueryString();

            return view('admin.events.index', compact('events', 'search', 'status'));
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