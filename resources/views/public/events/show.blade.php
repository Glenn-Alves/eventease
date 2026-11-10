@php
    $left = $event->total_slots - $event->registrations_count;
    $isExternal = $event->registration_mode === 'external';
    $open = $event->isRegistrationOpen();

    // Both pages need a login, so a visitor who is not logged in is sent to the login page first
    $registerUrl = $isExternal
        ? route('participant.events.show', $event)
        : route('participant.events.register.form', $event);
@endphp

<x-public-layout :title="$event->title.' | EventEase'">
    <div class="mx-auto max-w-3xl space-y-6 px-4 py-10 sm:px-6 lg:px-8">

        <a href="{{ route('home') }}" class="tap inline-flex items-center gap-1.5 text-sm text-gray-800 underline">
            <x-icon name="back" class="h-4 w-4" /> All events
        </a>

        <x-stepper :current="1" />

        <div class="overflow-hidden rounded-xl bg-white shadow">

            @if ($event->thumbnailUrl())
                <div class="relative">
                    <img src="{{ $event->thumbnailUrl() }}" alt="Poster of {{ $event->title }}" class="aspect-video w-full object-cover">

                    <x-countdown overlay class="absolute right-3 top-3" :to="$event->startsAt()->toIso8601String()"
                                 label="Starts in" done-text="Started" />
                </div>
            @endif

            <div class="p-6">
                <div class="flex flex-wrap items-center gap-2">
                    <x-badge>{{ \App\Models\Event::TYPES[$event->type] ?? 'Others' }}</x-badge>
                    <x-event-status :event="$event" />
                </div>

                <h1 class="mt-3 text-2xl font-bold text-slate-900">{{ $event->title }}</h1>

                <p class="mt-3 whitespace-pre-line text-gray-800">{{ $event->description }}</p>

                <dl class="mt-5 grid gap-4 text-sm sm:grid-cols-2">
                    <div class="flex items-start gap-3">
                        <x-icon name="calendar" class="mt-0.5 h-5 w-5 text-gray-600" />
                        <div>
                            <dt class="text-gray-600">Date and time</dt>
                            <dd class="font-medium text-gray-900">
                                {{ $event->event_date->format('F d, Y') }} at {{ \Carbon\Carbon::parse($event->event_time)->format('h:i A') }}
                            </dd>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <x-icon name="pin" class="mt-0.5 h-5 w-5 text-gray-600" />
                        <div>
                            <dt class="text-gray-600">Location</dt>
                            <dd class="font-medium text-gray-900">{{ $event->location }}</dd>
                        </div>
                    </div>

                    @unless ($isExternal)
                        <div class="flex items-start gap-3">
                            <x-icon name="ticket" class="mt-0.5 h-5 w-5 text-gray-600" />
                            <div>
                                <dt class="text-gray-600">Available slots</dt>
                                <dd class="font-medium text-gray-900">{{ max($left, 0) }} of {{ $event->total_slots }}</dd>
                            </div>
                        </div>
                    @endunless

                    <div class="flex items-start gap-3">
                        <x-icon name="lock" class="mt-0.5 h-5 w-5 text-gray-600" />
                        <div>
                            <dt class="text-gray-600">Registration</dt>
                            <dd class="font-medium text-gray-900">
                                @if ($event->registration_deadline)
                                    {{ $open ? 'Closes' : 'Closed' }} {{ $event->registration_deadline->format('F d, Y h:i A') }}
                                @else
                                    Open until the event starts
                                @endif
                            </dd>
                        </div>
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

                <div class="mt-6">
                    @guest
                        @if (! $open)
                            <x-badge tone="red" icon="lock">Registration is closed</x-badge>
                        @elseif (! $isExternal && $left <= 0)
                            <x-badge tone="red" icon="warning">This event is full</x-badge>
                        @else
                            <x-action :href="$registerUrl" icon="lock" variant="primary">Log in to register</x-action>
                            <p class="mt-2 text-xs text-gray-700">
                                You need an account to register. After you log in or sign up, you go straight to the registration.
                            </p>
                        @endif
                    @endguest

                    @auth
                        <p class="flex items-start gap-2 text-sm text-gray-800">
                            <x-icon name="info" class="mt-0.5 h-4 w-4" />
                            You are logged in with an {{ auth()->user()->role }} account. Only participant accounts can register for events.
                        </p>

                        @if (auth()->id() === $event->user_id)
                            <x-action :href="route('organizer.events.show', $event)" icon="edit" class="mt-3">Manage this event</x-action>
                        @endif
                    @endauth
                </div>
            </div>
        </div>

        <x-event-gallery :event="$event" />
    </div>
</x-public-layout>