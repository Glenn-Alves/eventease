@php
    $left = $event->total_slots - $event->registrations_count;
    $isExternal = $event->registration_mode === 'external' && $event->external_form_url;
    $open = $event->isRegistrationOpen();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-xl font-semibold text-gray-800">{{ $event->title }}</h2>
            <a href="{{ route('participant.dashboard') }}" class="tap inline-flex items-center gap-1.5 text-sm text-gray-800 underline">
                <x-icon name="back" class="h-4 w-4" /> Back to events
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl space-y-6 px-4 py-10 sm:px-6 lg:px-8">

        <x-stepper :current="$registration ? 3 : 1" />

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

                <x-input-error :messages="$errors->get('register')" class="mt-4" />

                <div class="mt-6">
                    @if ($registration)
                        <x-badge tone="green" icon="check">
                            You are registered{{ $registration->category ? ' in the '.$registration->category->name.' category' : '' }}
                        </x-badge>
                        <p class="mt-2 text-sm text-gray-800">
                            Your ticket code: <strong class="font-mono tracking-widest">{{ $registration->ticket_code }}</strong>
                        </p>
                        <x-action :href="route('participant.registrations.show', $registration)" icon="ticket" variant="primary" class="mt-3">
                            View my ticket
                        </x-action>
                    @elseif (! $open)
                        <x-badge tone="red" icon="lock">Registration is closed</x-badge>
                    @elseif ($isExternal)
                        <x-action :href="$event->external_form_url" icon="external" variant="primary" target="_blank">
                            Register on the organizer's form
                        </x-action>
                        <p class="mt-2 text-xs text-gray-700">This opens the organizer's own form in a new tab.</p>
                    @elseif ($left <= 0)
                        <x-badge tone="red" icon="warning">This event is full</x-badge>
                    @else
                        <x-action :href="route('participant.events.register.form', $event)" icon="ticket" variant="primary">
                            Register for this event
                        </x-action>
                    @endif
                </div>
            </div>
        </div>

        <x-event-gallery :event="$event" />
    </div>
</x-app-layout>