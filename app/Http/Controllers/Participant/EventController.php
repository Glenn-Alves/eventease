<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    private const ALREADY_REGISTERED = 'You are already registered for this event.';
    private const EVENT_FULL = 'Sorry, this event is full.';

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

        $user = auth()->user();

        $myCount = $user->registrations()->count();
        $registeredIds = $user->registrations()->pluck('event_id');

        $nextRegistered = $user->registrations()
            ->with('event')
            ->whereHas('event', fn ($query) => $query->whereDate('event_date', '>=', today()))
            ->get()
            ->sortBy(fn ($registration) => $registration->event->event_date)
            ->first();

        return view('participant.dashboard', compact(
            'events', 'type', 'counts', 'myCount', 'registeredIds', 'nextRegistered'
        ));
    }

    // Event details page: thumbnail, description, when and where, and the Register button
    public function show(Event $event)
    {
        $event->loadCount('registrations');

        $registration = $event->registrations()->with('category')
            ->where('user_id', auth()->id())->first();

        return view('participant.events.show', compact('event', 'registration'));
    }

    // The separate page that shows only the organizer's form
    public function registerForm(Event $event)
    {
        if ($event->registration_mode === 'external') {
            return redirect()->route('participant.events.show', $event);
        }

        $event->loadCount('registrations');
        $event->load([
            'fields',
            'categories' => fn ($query) => $query->withCount('registrations'),
        ]);

        $already = $event->registrations()->where('user_id', auth()->id())->exists();

        if ($already || $event->registrations_count >= $event->total_slots) {
            return redirect()->route('participant.events.show', $event);
        }

        return view('participant.events.register', [
            'event' => $event,
            'preview' => false,
        ]);
    }

    public function register(Request $request, Event $event)
    {
        abort_if($event->registration_mode === 'external', 403, 'This event uses an external registration form.');

        // Section headings and notes are not questions
        $fields = $event->fields()->get()
            ->reject(fn ($field) => in_array($field->type, ['section', 'note']));

        $categories = $event->supportsCategories() ? $event->categories()->get() : collect();

        $rules = [];
        $attributes = [];

        if ($categories->isNotEmpty()) {
            $rules['category_id'] = ['required', Rule::in($categories->pluck('id')->all())];
            $attributes['category_id'] = 'category';
        }

        foreach ($fields as $field) {
            $key = 'answers.'.$field->id;
            $attributes[$key] = $field->label;
            $presence = $field->is_required ? 'required' : 'nullable';

            switch ($field->type) {
                case 'number':
                    $rules[$key] = [$presence, 'numeric'];
                    break;
                case 'date':
                    $rules[$key] = [$presence, 'date'];
                    break;
                case 'dropdown':
                case 'radio':
                    $rules[$key] = [$presence, 'string', Rule::in($field->options ?? [])];
                    break;
                case 'checkbox':
                    $rules[$key] = [$presence, 'array'];
                    $rules[$key.'.*'] = ['string', Rule::in($field->options ?? [])];
                    break;
                case 'textarea':
                    $rules[$key] = [$presence, 'string', 'max:5000'];
                    break;
                default:
                    $rules[$key] = [$presence, 'string', 'max:255'];
            }
        }

        $request->validate($rules, [], $attributes);

        $answers = $request->input('answers', []);
        $categoryId = $event->supportsCategories() ? $request->input('category_id') : null;

        $error = DB::transaction(function () use ($event, $fields, $answers, $categoryId) {
            // Lock rows so two people cannot take the last slot at the same time
            $event = Event::whereKey($event->id)->lockForUpdate()->first();

            if ($event->registrations()->where('user_id', auth()->id())->exists()) {
                return self::ALREADY_REGISTERED;
            }

            if ($event->registrations()->count() >= $event->total_slots) {
                return self::EVENT_FULL;
            }

            $category = null;

            if ($categoryId) {
                $category = EventCategory::whereKey($categoryId)
                    ->where('event_id', $event->id)
                    ->lockForUpdate()
                    ->first();

                if (! $category) {
                    return 'Please choose a valid category.';
                }

                if ($category->isClosed()) {
                    return 'Registration for this category is closed.';
                }

                if ($category->registrations()->count() >= $category->slots) {
                    return 'Sorry, this category is full.';
                }
            }

            $registration = Registration::create([
                'user_id' => auth()->id(),
                'event_id' => $event->id,
                'event_category_id' => $category?->id,
                'ticket_code' => strtoupper(Str::random(10)),
            ]);

            foreach ($fields as $field) {
                $value = $answers[$field->id] ?? null;

                if (is_array($value)) {
                    $value = implode(', ', $value);
                }

                $registration->answers()->create([
                    'event_field_id' => $field->id,
                    'answer' => $value,
                ]);
            }

            return null;
        });

        if ($error) {
            $response = in_array($error, [self::ALREADY_REGISTERED, self::EVENT_FULL], true)
                ? redirect()->route('participant.events.show', $event)
                : back()->withInput();

            return $response->withErrors(['register' => $error]);
        }

        return redirect()->route('participant.registrations.index')
            ->with('status', 'Registration successful!');
    }

    public function myRegistrations()
{
    $registrations = auth()->user()->registrations()
        ->with(['event', 'category'])
        ->get();

    $upcoming = $registrations
        ->filter(fn ($registration) => ! $registration->event->event_date->isBefore(today()))
        ->sortBy(fn ($registration) => $registration->event->event_date)
        ->values();

    $past = $registrations
        ->filter(fn ($registration) => $registration->event->event_date->isBefore(today()))
        ->sortByDesc(fn ($registration) => $registration->event->event_date)
        ->values();

    return view('participant.registrations', compact('upcoming', 'past'));
}
}