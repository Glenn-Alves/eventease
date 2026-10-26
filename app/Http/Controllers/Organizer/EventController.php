<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type');
        $type = ($type && isset(Event::TYPES[$type])) ? $type : null;

        $mine = Event::where('user_id', auth()->id());

        $counts = (clone $mine)
            ->selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $events = $mine
            ->when($type, fn ($query) => $query->where('type', $type))
            ->orderBy('event_date')
            ->get();

        return view('organizer.events.index', compact('events', 'type', 'counts'));
    }

    public function create()
    {
        return view('organizer.events.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $attributes = Arr::except($data, ['thumbnail', 'remove_thumbnail']);

        if ($request->hasFile('thumbnail')) {
            $attributes['thumbnail'] = $request->file('thumbnail')->store('events', 'public');
        }

        $event = $request->user()->events()->create($attributes);

        return redirect()->route('organizer.events.form.edit', $event)
            ->with('status', 'Event created. Now design your registration form.');
    }

    public function edit(Event $event)
    {
        $this->checkOwner($event);
        return view('organizer.events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $this->checkOwner($event);
        $data = $this->validated($request);
        $attributes = Arr::except($data, ['thumbnail', 'remove_thumbnail']);

        if ($request->hasFile('thumbnail')) {
            $this->deleteThumbnail($event);
            $attributes['thumbnail'] = $request->file('thumbnail')->store('events', 'public');
        } elseif ($request->boolean('remove_thumbnail')) {
            $this->deleteThumbnail($event);
            $attributes['thumbnail'] = null;
        }

        $event->update($attributes);

        return redirect()->route('organizer.events.index')->with('status', 'Event updated.');
    }

    public function destroy(Event $event)
    {
        $this->checkOwner($event);
        $this->deleteThumbnail($event);
        $event->delete();

        return redirect()->route('organizer.events.index')->with('status', 'Event deleted.');
    }

    private function checkOwner(Event $event): void
    {
        abort_unless($event->user_id === auth()->id(), 403);
    }

    private function deleteThumbnail(Event $event): void
    {
        if ($event->thumbnail) {
            Storage::disk('public')->delete($event->thumbnail);
        }
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys(Event::TYPES))],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_thumbnail' => ['nullable', 'boolean'],
            'description' => ['required', 'string'],
            'event_date' => ['required', 'date'],
            'event_time' => ['required', 'date_format:H:i'],
            'location' => ['required', 'string', 'max:255'],
            'total_slots' => ['required', 'integer', 'min:1'],
        ], [
            'thumbnail.image' => 'The thumbnail must be an image.',
            'thumbnail.max' => 'The thumbnail must be 2 MB or smaller.',
        ]);
    }
}