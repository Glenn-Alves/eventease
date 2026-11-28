@php
    $event = $registration->event;
    $firstName = explode(' ', trim(auth()->user()->name))[0];
    $category = $registration->category;
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Registration complete</h2>
    </x-slot>

    <style>
        .thanks-qr svg { display: block; width: 100%; height: auto; }

        @media (prefers-reduced-motion: no-preference) {
            .thanks-pop { animation: thanks-pop .5s ease-out both; }

            @keyframes thanks-pop {
                0% { transform: scale(.6); opacity: 0; }
                70% { transform: scale(1.08); opacity: 1; }
                100% { transform: scale(1); opacity: 1; }
            }
        }
    </style>

    <div class="mx-auto max-w-2xl space-y-6 px-4 py-10 sm:px-6 lg:px-8">

        {{-- Thank you --}}
        <div class="rounded-xl bg-white p-8 text-center shadow">
            <span class="thanks-pop mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-50 text-green-700">
                <x-icon name="check" class="h-9 w-9" />
            </span>

            <h1 class="mt-5 text-2xl font-bold text-slate-900 sm:text-3xl">Thank you, {{ $firstName }}!</h1>
            <p class="mt-2 text-gray-800">You are registered for <strong>{{ $event->title }}</strong>.</p>

            @if ($event->startsInLabel())
                <p class="mt-1 text-sm text-gray-700">{{ $event->startsInLabel() }}</p>
            @endif
        </div>

        {{-- Ticket summary --}}
        <div class="overflow-hidden rounded-xl bg-white shadow">
            @if ($event->thumbnailUrl())
                <img src="{{ $event->thumbnailUrl() }}" alt="Poster of {{ $event->title }}" class="aspect-video w-full object-cover">
            @endif

            <div class="grid gap-6 p-6 sm:grid-cols-[1fr_auto]">
                <div class="space-y-2 text-sm text-gray-800">
                    <x-badge>{{ \App\Models\Event::TYPES[$event->type] ?? 'Others' }}</x-badge>

                    <h2 class="text-xl font-bold text-slate-900">{{ $event->title }}</h2>

                    <p class="flex items-center gap-2">
                        <x-icon name="calendar" class="h-4 w-4 shrink-0 text-gray-600" />
                        {{ $event->event_date->format('l, F d, Y') }}
                    </p>
                    <p class="flex items-center gap-2">
                        <x-icon name="clock" class="h-4 w-4 shrink-0 text-gray-600" />
                        {{ \Carbon\Carbon::parse($event->event_time)->format('h:i A') }}
                    </p>
                    <p class="flex items-center gap-2">
                        <x-icon name="pin" class="h-4 w-4 shrink-0 text-gray-600" />
                        {{ $event->location }}
                    </p>

                    @if ($category)
                        <p class="flex items-center gap-2">
                            <x-icon name="ticket" class="h-4 w-4 shrink-0 text-gray-600" />
                            {{ $category->name }}
                            @if ($category->price > 0)
                                | PHP {{ number_format($category->price, 2) }}
                            @endif
                        </p>
                    @endif
                </div>

                <div class="mx-auto w-36 max-w-full text-center">
                    <div class="thanks-qr" role="img" aria-label="QR code of your ticket">{!! $qr !!}</div>
                    <p class="mt-1 text-xs text-gray-700">Show this at the entrance</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-dashed border-gray-300 bg-gray-50 px-6 py-4"
                 x-data="{ copied: false }">
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-700">Ticket code</p>
                    <p class="font-mono text-2xl font-bold tracking-widest text-gray-900">{{ $registration->ticket_code }}</p>
                </div>

                <button type="button"
                        x-on:click="navigator.clipboard.writeText(@js($registration->ticket_code)); copied = true; setTimeout(() => copied = false, 1500)"
                        class="tap rounded-md border border-slate-900 bg-white px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-slate-50"
                        x-text="copied ? 'Copied' : 'Copy code'"></button>
            </div>

            @if ($category && $category->price > 0)
                <p class="border-t px-6 py-3 text-xs text-gray-700">
                    The price is shown for your information. EventEase does not take payments, so ask the organizer how to pay.
                </p>
            @endif
        </div>

        {{-- What happens next --}}
        <section class="rounded-xl bg-white p-6 shadow">
            <h2 class="text-lg font-semibold text-slate-900">What happens next</h2>

            <ol class="mt-4 space-y-4 text-sm text-gray-800">
                <li class="flex gap-3">
                    <x-icon name="ticket" class="mt-0.5 h-5 w-5 shrink-0 text-indigo-700" />
                    <p>
                        <strong>Keep your ticket.</strong> It is saved under My tickets. You can print it or show the QR code on your phone.
                        Your ticket is personal, so do not post the QR code in public.
                    </p>
                </li>
                <li class="flex gap-3">
                    <x-icon name="calendar" class="mt-0.5 h-5 w-5 shrink-0 text-indigo-700" />
                    <p>
                        <strong>Arrive on time.</strong>
                        {{ $event->event_date->format('F d') }} at {{ \Carbon\Carbon::parse($event->event_time)->format('h:i A') }},
                        {{ $event->location }}. Add it to your calendar so you do not forget.
                    </p>
                </li>
                <li class="flex gap-3">
                    <x-icon name="qr" class="mt-0.5 h-5 w-5 shrink-0 text-indigo-700" />
                    <p>
                        <strong>Check in at the entrance.</strong> The organizer scans your QR code or types your ticket code.
                        Bring a valid ID, because the organizer may check it.
                    </p>
                </li>
            </ol>
        </section>

        {{-- Buttons --}}
        <div class="flex flex-wrap justify-center gap-3">
            <x-action :href="route('participant.registrations.show', $registration)" icon="ticket" variant="primary">View my ticket</x-action>
            <x-action :href="route('participant.registrations.calendar', $registration)" icon="calendar">Add to calendar</x-action>
            <x-action :href="route('participant.dashboard')" icon="search">Find more events</x-action>
        </div>
    </div>
</x-app-layout>