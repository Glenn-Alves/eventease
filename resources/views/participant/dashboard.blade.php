
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <h2 class="font-semibold text-xl text-gray-800">Participant Dashboard</h2>

            <a href="{{ route('participant.registrations.index') }}"
               class="px-4 py-2 border border-gray-800 text-sm rounded-md">
                My Registrations
            </a>
        </div>
    </x-slot>

    <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <p class="text-gray-600">
            Welcome, {{ auth()->user()->name }}. Find an event and register.
        </p>

        {{-- Quick numbers --}}
        <div class="grid gap-4 sm:grid-cols-2">

            <div class="bg-white p-5 rounded shadow">
                <p class="text-sm text-gray-500">My registrations</p>
                <p class="text-3xl font-bold">{{ $myCount }}</p>
            </div>

            <div class="bg-white p-5 rounded shadow">
                <p class="text-sm text-gray-500">My next event</p>

                @if ($nextRegistered)
                    <p class="font-semibold mt-1">
                        {{ $nextRegistered->event->title }}
                    </p>

                    <p class="text-sm text-gray-600">
                        {{ $nextRegistered->event->event_date->format('M d, Y') }} at
                        {{ \Carbon\Carbon::parse($nextRegistered->event->event_time)->format('h:i A') }}
                    </p>
                @else
                    <p class="text-sm text-gray-500 mt-1">
                        You have no upcoming registered events.
                    </p>
                @endif
            </div>

        </div>

        {{-- Menu and events --}}
        <div class="flex flex-col md:flex-row gap-6">

            <x-event-type-menu
                route-name="participant.dashboard"
                :type="$type"
                :counts="$counts"
            />

            <div class="flex-1 min-w-0 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 content-start">

                @forelse ($events as $event)
                    <x-event-card
                        :event="$event"
                        :href="route('participant.events.show', $event)"
                        :registered="$registeredIds->contains($event->id)"
                    />
                @empty
                    <x-empty-state
                        class="col-span-full"
                        icon="calendar"
                        title="No upcoming events here yet"
                        text="Pick another type from the menu, or come back later."
                    />
                @endforelse

            </div>
        </div>
    </div>
</x-app-layout>
