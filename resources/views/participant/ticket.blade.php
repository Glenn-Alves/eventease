
@php $event = $registration->event; @endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <h2 class="font-semibold text-xl text-gray-800">My Ticket</h2>
            <a href="{{ route('participant.registrations.index') }}" class="text-sm underline text-gray-600">All my registrations</a>
        </div>
    </x-slot>

    <style>
        .ticket-qr svg { display: block; width: 100%; height: auto; }

        @media print {
            nav, header, .no-print { display: none !important; }
            body { background: #fff !important; }
        }
    </style>

    <div class="py-12 max-w-md mx-auto sm:px-6 lg:px-8">

        {{-- Stepper and flash messages --}}
        @if (session('status'))
            <x-stepper class="no-print mb-4" :current="3" />
        @endif

        <x-flash class="no-print mb-4" />

        <div class="bg-white rounded-2xl shadow overflow-hidden">

            @if ($event->thumbnailUrl())
                <img src="{{ $event->thumbnailUrl() }}" alt="{{ $event->title }}" class="w-full aspect-video object-cover">
            @endif

            <div class="p-6">
                <span class="text-xs px-2 py-1 bg-gray-100 rounded">
                    {{ \App\Models\Event::TYPES[$event->type] ?? 'Others' }}
                </span>

                <h3 class="text-xl font-bold mt-2">{{ $event->title }}</h3>

                <p class="text-sm text-gray-600 mt-1">
                    {{ $event->event_date->format('l, F d, Y') }} at
                    {{ \Carbon\Carbon::parse($event->event_time)->format('h:i A') }}
                </p>

                <p class="text-sm text-gray-600">{{ $event->location }}</p>

                <x-countdown
                    class="mt-4"
                    :to="$event->startsAt()->toIso8601String()"
                    label="Event starts in"
                    done-text="The event has started"
                />
            </div>

            {{-- Ticket tear line --}}
            <div class="relative border-t-2 border-dashed border-gray-300 mx-6">
                <span class="absolute -top-3 -left-9 w-6 h-6 rounded-full bg-gray-100"></span>
                <span class="absolute -top-3 -right-9 w-6 h-6 rounded-full bg-gray-100"></span>
            </div>

            <div class="p-6 text-center">
                {{-- Ticket status --}}
                @if ($registration->checked_in_at)
                    <x-badge tone="gray" icon="check">
                        Checked in {{ $registration->checked_in_at->format('M d, h:i A') }}
                    </x-badge>
                @else
                    <x-badge tone="green" icon="ticket">Valid ticket</x-badge>
                @endif

                <div class="ticket-qr mx-auto mt-4 w-56 max-w-full">{!! $qr !!}</div>

                <p class="mt-3 font-mono text-2xl font-bold tracking-widest text-gray-800">
                    {{ $registration->ticket_code }}
                </p>

                <p class="mt-1 text-xs text-gray-500">
                    Show this QR code at the entrance.
                </p>

                <dl class="mt-5 pt-4 border-t text-sm text-left space-y-1">
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">Name</dt>
                        <dd class="font-medium text-right">{{ auth()->user()->name }}</dd>
                    </div>

                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">Email</dt>
                        <dd class="font-medium text-right">{{ auth()->user()->email }}</dd>
                    </div>

                    @if ($registration->category)
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500">Category</dt>
                            <dd class="font-medium text-right">{{ $registration->category->name }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>

        {{-- Ticket actions --}}
        <div class="no-print mt-4 grid gap-3 sm:grid-cols-2">
            <x-action icon="download" variant="primary" onclick="window.print()">
                Print or save as PDF
            </x-action>

            <x-action :href="route('participant.events.show', $event)" icon="eye">
                View event
            </x-action>
        </div>
    </div>
</x-app-layout>
