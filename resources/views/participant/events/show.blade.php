@php
    $left = $event->total_slots - $event->registrations_count;
    $isExternal = $event->registration_mode === 'external' && $event->external_form_url;
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
                <img src="{{ $event->thumbnailUrl() }}" alt="{{ $event->title }}" class="w-full aspect-video object-cover">
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
                </dl>

                <x-input-error :messages="$errors->get('register')" class="mt-4" />

                <div class="mt-6">
                    @if ($registration)
                        <p class="text-green-700">
                            You are registered{{ $registration->category ? ' in the '.$registration->category->name.' category' : '' }}.
                            Your ticket code: <strong>{{ $registration->ticket_code }}</strong>
                        </p>
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
    </div>
</x-app-layout>