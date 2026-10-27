<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">
                My {{ $type ? \App\Models\Event::TYPES[$type] : '' }} Events
            </h2>
            <a href="{{ route('organizer.events.create') }}" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-md">New Event</a>
        </div>
    </x-slot>

    <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col md:flex-row gap-6">

        <x-event-type-menu route-name="organizer.events.index" :type="$type" :counts="$counts" />

        <div class="flex-1 min-w-0">
            @if (session('status'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($events as $event)
                    @php
                        $taken = $event->registrations_count;
                        $percent = $event->total_slots > 0 ? min(100, round($taken / $event->total_slots * 100)) : 0;
                    @endphp

                    <div class="bg-white rounded shadow overflow-hidden flex flex-col">
                        @if ($event->thumbnailUrl())
                            <img src="{{ $event->thumbnailUrl() }}" alt="{{ $event->title }}" class="w-full aspect-video object-cover">
                        @else
                            <div class="w-full aspect-video bg-gray-200 flex items-center justify-center text-sm text-gray-500">No image</div>
                        @endif

                        <div class="p-5 flex flex-col flex-1">
                            <span class="self-start text-xs px-2 py-1 bg-gray-100 rounded">
                                {{ \App\Models\Event::TYPES[$event->type] ?? 'Others' }}
                            </span>

                            <h3 class="font-semibold text-lg mt-2">{{ $event->title }}</h3>

                            <p class="text-sm text-gray-600 mt-1">
                                {{ $event->event_date->format('M d, Y') }} at
                                {{ \Carbon\Carbon::parse($event->event_time)->format('h:i A') }}
                            </p>
                            <p class="text-sm text-gray-600">{{ $event->location }}</p>

                            <div class="mt-3">
                                @if ($event->registration_mode === 'external')
                                    <p class="text-sm text-gray-500">Uses an external form</p>
                                @else
                                    <div class="flex justify-between text-xs text-gray-600">
                                        <span>{{ $taken }} of {{ $event->total_slots }} registered</span>
                                        <span>{{ $percent }}%</span>
                                    </div>
                                    <div class="mt-1 h-2 bg-gray-200 rounded">
                                        <div class="h-2 bg-gray-800 rounded" style="width: {{ $percent }}%"></div>
                                    </div>
                                @endif
                            </div>

                            <a href="{{ route('organizer.events.show', $event) }}"
                               class="mt-auto pt-4 block">
                                <span class="block text-center px-4 py-2 bg-gray-800 text-white text-sm rounded-md">View</span>
                            </a>
                        </div>
                    </div>
                @empty
                    <p class="col-span-full text-gray-500">No events here yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>