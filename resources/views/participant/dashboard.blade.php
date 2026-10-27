<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <h2 class="font-semibold text-xl text-gray-800">Participant Dashboard</h2>
            <a href="{{ route('participant.registrations.index') }}"
               class="px-4 py-2 border border-gray-800 text-sm rounded-md">My Registrations</a>
        </div>
    </x-slot>

    <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <p class="text-gray-600">Welcome, {{ auth()->user()->name }}. Find an event and register.</p>

        {{-- Quick numbers --}}
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="bg-white p-5 rounded shadow">
                <p class="text-sm text-gray-500">My registrations</p>
                <p class="text-3xl font-bold">{{ $myCount }}</p>
            </div>

            <div class="bg-white p-5 rounded shadow">
                <p class="text-sm text-gray-500">My next event</p>
                @if ($nextRegistered)
                    <p class="font-semibold mt-1">{{ $nextRegistered->event->title }}</p>
                    <p class="text-sm text-gray-600">
                        {{ $nextRegistered->event->event_date->format('M d, Y') }} at
                        {{ \Carbon\Carbon::parse($nextRegistered->event->event_time)->format('h:i A') }}
                    </p>
                @else
                    <p class="text-sm text-gray-500 mt-1">You have no upcoming registered events.</p>
                @endif
            </div>
        </div>

        {{-- Menu and events --}}
        <div class="flex flex-col md:flex-row gap-6">

            <x-event-type-menu route-name="participant.dashboard" :type="$type" :counts="$counts" />

            <div class="flex-1 min-w-0 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 content-start">
                @forelse ($events as $event)
                    @php $left = $event->total_slots - $event->registrations_count; @endphp

                    <div class="bg-white rounded shadow overflow-hidden flex flex-col">
                        @if ($event->thumbnailUrl())
                            <img src="{{ $event->thumbnailUrl() }}" alt="{{ $event->title }}" class="w-full aspect-video object-cover">
                        @else
                            <div class="w-full aspect-video bg-gray-200 flex items-center justify-center text-sm text-gray-500">No image</div>
                        @endif

                        <div class="p-5 flex flex-col flex-1">
                            <div class="flex flex-wrap gap-2">
                                <span class="text-xs px-2 py-1 bg-gray-100 rounded">
                                    {{ \App\Models\Event::TYPES[$event->type] ?? 'Others' }}
                                </span>

                                @if ($registeredIds->contains($event->id))
                                    <span class="text-xs px-2 py-1 bg-green-100 text-green-800 rounded">Registered</span>
                                @endif
                            </div>

                            <h3 class="font-semibold text-lg mt-2">{{ $event->title }}</h3>

                            <p class="text-sm text-gray-600 mt-1">
                                {{ $event->event_date->format('M d, Y') }} at
                                {{ \Carbon\Carbon::parse($event->event_time)->format('h:i A') }}
                            </p>
                            <p class="text-sm text-gray-600">{{ $event->location }}</p>

                            @if ($event->registration_mode === 'external')
                                <p class="mt-2 text-sm text-gray-500">Registration on the organizer's own form</p>
                            @else
                                <p class="mt-2 text-sm {{ $left > 0 ? 'text-green-700' : 'text-red-600' }}">
                                    {{ $left > 0 ? $left.' slots left' : 'Full' }}
                                </p>
                            @endif

                            <a href="{{ route('participant.events.show', $event) }}" class="mt-auto pt-4 block">
                                <span class="block text-center px-4 py-2 bg-gray-800 text-white text-sm rounded-md">View</span>
                            </a>
                        </div>
                    </div>
                @empty
                    <p class="col-span-full text-gray-500">No upcoming events here yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>