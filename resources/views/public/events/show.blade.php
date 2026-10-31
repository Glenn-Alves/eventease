@php
    $left = $event->total_slots - $event->registrations_count;
    $isExternal = $event->registration_mode === 'external';

    // Both pages need a login, so a visitor who is not logged in is sent to the login page first
    $registerUrl = $isExternal
        ? route('participant.events.show', $event)
        : route('participant.events.register.form', $event);
@endphp

<x-public-layout :title="$event->title.' | EventEase'">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <a href="{{ route('home') }}" class="text-sm underline text-gray-600">&larr; All events</a>

        <div class="mt-4 bg-white rounded-xl shadow overflow-hidden">
            @if ($event->thumbnailUrl())
                <img src="{{ $event->thumbnailUrl() }}" alt="{{ $event->title }}" class="w-full aspect-video object-cover">
            @endif

            <div class="p-6">
                <span class="inline-block text-xs px-2 py-1 bg-gray-100 rounded">
                    {{ \App\Models\Event::TYPES[$event->type] ?? 'Others' }}
                </span>

                <h1 class="mt-2 text-2xl font-bold text-slate-900">{{ $event->title }}</h1>

                <p class="mt-3 text-gray-700 whitespace-pre-line">{{ $event->description }}</p>

                <dl class="mt-4 text-sm space-y-1">
                    <div><strong>Date:</strong> {{ $event->event_date->format('F d, Y') }}</div>
                    <div><strong>Time:</strong> {{ \Carbon\Carbon::parse($event->event_time)->format('h:i A') }}</div>
                    <div><strong>Location:</strong> {{ $event->location }}</div>
                    @unless ($isExternal)
                        <div><strong>Available slots:</strong> {{ max($left, 0) }} of {{ $event->total_slots }}</div>
                    @endunless
                </dl>

                <div class="mt-6">
                    @guest
                        @if (! $isExternal && $left <= 0)
                            <p class="text-red-600">This event is full.</p>
                        @else
                            <a href="{{ $registerUrl }}"
                               class="inline-block px-6 py-3 bg-gray-800 text-white rounded-md">Log in to register</a>
                            <p class="mt-2 text-xs text-gray-500">
                                You need an account to register. After you log in or sign up, you go straight to the registration.
                            </p>
                        @endif
                    @endguest

                    @auth
                        <p class="text-sm text-gray-600">
                            You are logged in with an organizer account. Only participant accounts can register for events.
                        </p>

                        @if (auth()->id() === $event->user_id)
                            <a href="{{ route('organizer.events.show', $event) }}"
                               class="inline-block mt-3 px-5 py-2 bg-gray-800 text-white text-sm rounded-md">Manage this event</a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </div>
</x-public-layout>