<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">Organizer Dashboard</h2>
            <a href="{{ route('organizer.events.create') }}" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-md">New Event</a>
        </div>
    </x-slot>

    <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <p class="text-gray-600">Welcome back, {{ auth()->user()->name }}.</p>

        {{-- Numbers --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="bg-white p-5 rounded shadow">
                <p class="text-sm text-gray-500">My events</p>
                <p class="text-3xl font-bold">{{ $totalEvents }}</p>
            </div>
            <div class="bg-white p-5 rounded shadow">
                <p class="text-sm text-gray-500">Upcoming events</p>
                <p class="text-3xl font-bold">{{ $upcomingCount }}</p>
            </div>
            <div class="bg-white p-5 rounded shadow">
                <p class="text-sm text-gray-500">Total registrations</p>
                <p class="text-3xl font-bold">{{ $totalRegistrations }}</p>
            </div>
            <div class="bg-white p-5 rounded shadow">
                <p class="text-sm text-gray-500">Slots left (upcoming)</p>
                <p class="text-3xl font-bold">{{ $slotsLeft }}</p>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">

            {{-- Upcoming events --}}
            <div class="bg-white p-6 rounded shadow">
                <div class="flex justify-between items-center">
                    <h3 class="font-semibold text-lg">Upcoming events</h3>
                    <a href="{{ route('organizer.events.index') }}" class="text-sm underline text-gray-600">See all</a>
                </div>

                <div class="mt-4 space-y-4">
                    @forelse ($nextEvents as $event)
                        @php
                            $percent = $event->total_slots > 0
                                ? min(100, round($event->registrations_count / $event->total_slots * 100))
                                : 0;
                        @endphp

                        <div>
                            <div class="flex justify-between gap-3">
                                <a href="{{ route('organizer.events.show', $event) }}" class="font-medium hover:underline">{{ $event->title }}</a>
                                <span class="text-sm text-gray-500 whitespace-nowrap">{{ $event->event_date->format('M d, Y') }}</span>
                            </div>

                            @if ($event->registration_mode === 'external')
                                <p class="text-xs text-gray-500 mt-1">Uses an external form</p>
                            @else
                                <div class="flex justify-between text-xs text-gray-600 mt-1">
                                    <span>{{ $event->registrations_count }} of {{ $event->total_slots }} registered</span>
                                    <span>{{ $percent }}%</span>
                                </div>
                                <div class="mt-1 h-2 bg-gray-200 rounded">
                                    <div class="h-2 bg-gray-800 rounded" style="width: {{ $percent }}%"></div>
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">
                            No upcoming events.
                            <a href="{{ route('organizer.events.create') }}" class="underline">Create one</a>.
                        </p>
                    @endforelse
                </div>
            </div>

            {{-- Recent registrations --}}
            <div class="bg-white p-6 rounded shadow">
                <h3 class="font-semibold text-lg">Recent registrations</h3>

                <div class="mt-4 divide-y">
                    @forelse ($recent as $registration)
                        <div class="py-3 flex justify-between gap-3 text-sm">
                            <div>
                                <p class="font-medium">{{ $registration->user->name }}</p>
                                <p class="text-gray-500">
                                    {{ $registration->event->title }}
                                    @if ($registration->category)
                                        | {{ $registration->category->name }}
                                    @endif
                                </p>
                            </div>
                            <span class="text-xs text-gray-500 whitespace-nowrap">{{ $registration->created_at->diffForHumans() }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 py-3">No registrations yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>