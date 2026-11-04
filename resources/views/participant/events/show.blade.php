@php
    $left = $event->total_slots - $event->registrations_count;
    $isExternal = $event->registration_mode === 'external' && $event->external_form_url;
    $open = $event->isRegistrationOpen();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <h2 class="font-semibold text-xl text-gray-800">{{ $event->title }}</h2>
            <a href="{{ route('participant.dashboard') }}" class="text-sm underline text-gray-600">Back to events</a>
        </div>
    </x-slot>

    <div class="py-12 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded shadow overflow-hidden">

            

@if ($event->thumbnailUrl())
    <div style="position: relative; width: 100%; overflow: hidden;">

        <img
            src="{{ $event->thumbnailUrl() }}"
            alt="{{ $event->title }}"
            style="width: 100%; aspect-ratio: 16 / 9; object-fit: cover; display: block;"
        >

        <div
            style="
                position: absolute;
                top: 10px;
                right: 10px;
                z-index: 999;
                transform: scale(0.6);
                transform-origin: top right;
                transition: opacity 0.3s ease;
            "
            onmouseover="this.style.opacity='0.3'"
            onmouseout="this.style.opacity='1'"
        >
            <x-countdown
                :to="$event->startsAt()->toIso8601String()"
                label="Starts in"
                done-text="Started"
            />
        </div>

    </div>
@endif



            <div class="p-6">
                <span class="inline-block text-xs px-2 py-1 bg-gray-100 rounded">
                    {{ \App\Models\Event::TYPES[$event->type] ?? 'Others' }}
                </span>

                <p class="mt-3 text-gray-700 whitespace-pre-line">{{ $event->description }}</p>

                <dl class="mt-4 text-sm space-y-1">
                    <div><strong>Date:</strong> {{ $event->event_date->format('F d, Y') }}</div>
                    <div><strong>Time:</strong> {{ \Carbon\Carbon::parse($event->event_time)->format('h:i A') }}</div>
                    <div><strong>Location:</strong> {{ $event->location }}</div>
                    @unless ($isExternal)
                        <div><strong>Available slots:</strong> {{ max($left, 0) }} of {{ $event->total_slots }}</div>
                    @endunless
                    <div>
                        <strong>Registration:</strong>
                        @if ($event->registration_deadline)
                            {{ $open ? 'closes' : 'closed' }} {{ $event->registration_deadline->format('F d, Y h:i A') }}
                        @else
                            open until the event starts
                        @endif
                    </div>
                </dl>

                @if (! $event->thumbnailUrl() || ($event->registration_deadline && $open))
                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        @unless ($event->thumbnailUrl())
                            <x-countdown :to="$event->startsAt()->toIso8601String()"
                                        label="Event starts in" done-text="The event has started" />
                        @endunless

                        @if ($event->registration_deadline && $open)
                            <x-countdown :to="$event->registration_deadline->toIso8601String()"
                                        label="Registration closes in" done-text="Registration closed" />
                        @endif
                    </div>
                @endif

                <x-input-error :messages="$errors->get('register')" class="mt-4" />

                <div class="mt-6">
                    @if ($registration)
                        <p class="text-green-700">
                            You are registered{{ $registration->category ? ' in the '.$registration->category->name.' category' : '' }}.
                            Your ticket code: <strong>{{ $registration->ticket_code }}</strong>
                        </p>
                        <a href="{{ route('participant.registrations.show', $registration) }}"
                           class="inline-block mt-3 px-5 py-2 bg-gray-800 text-white text-sm rounded-md">View ticket</a>
                    @elseif (! $open)
                        <p class="font-medium text-red-600">Registration is closed.</p>
                    @elseif ($isExternal)
                        <a href="{{ $event->external_form_url }}" target="_blank" rel="noopener noreferrer"
                           class="inline-block px-6 py-3 bg-gray-800 text-white rounded-md">Register</a>
                        <p class="mt-2 text-xs text-gray-500">This opens the organizer's own form in a new tab.</p>
                    @elseif ($left <= 0)
                        <p class="text-red-600">This event is full.</p>
                    @else
                        <a href="{{ route('participant.events.register.form', $event) }}"
                           class="inline-block px-6 py-3 bg-gray-800 text-white rounded-md">Register</a>
                    @endif
                </div>
            </div>
        </div>
        <x-event-gallery :event="$event" class="mt-6" />
    </div>
</x-app-layout>