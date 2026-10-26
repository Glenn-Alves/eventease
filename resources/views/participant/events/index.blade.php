<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">
            {{ $type ? \App\Models\Event::TYPES[$type] : 'All' }} Events
        </h2>
    </x-slot>

    <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col md:flex-row gap-6">

        <x-event-type-menu route-name="participant.events.index" :type="$type" :counts="$counts" />

        <div class="flex-1 grid gap-6 sm:grid-cols-2 xl:grid-cols-3 content-start">
            @forelse ($events as $event)
                @php $left = $event->total_slots - $event->registrations_count; @endphp

                <div class="bg-white rounded shadow overflow-hidden">
                    @if ($event->thumbnailUrl())
                        <img src="{{ $event->thumbnailUrl() }}" alt="{{ $event->title }}" class="w-full aspect-video object-cover">
                    @else
                        <div class="w-full aspect-video bg-gray-200 flex items-center justify-center text-sm text-gray-500">No image</div>
                    @endif

                    <div class="p-5">
                        <span class="inline-block text-xs px-2 py-1 bg-gray-100 rounded">
                            {{ \App\Models\Event::TYPES[$event->type] ?? 'Others' }}
                        </span>

                        <h3 class="font-semibold text-lg mt-2">{{ $event->title }}</h3>
                        <p class="text-sm text-gray-600 mt-1">
                            {{ $event->event_date->format('M d, Y') }} at
                            {{ \Carbon\Carbon::parse($event->event_time)->format('h:i A') }}
                        </p>
                        <p class="text-sm text-gray-600">{{ $event->location }}</p>
                        <p class="mt-2 text-sm {{ $left > 0 ? 'text-green-700' : 'text-red-600' }}">
                            {{ $left > 0 ? $left.' slots left' : 'Full' }}
                        </p>

                        <a href="{{ route('participant.events.show', $event) }}"
                           class="inline-block mt-4 px-4 py-2 bg-gray-800 text-white text-sm rounded-md">View Details</a>
                    </div>
                </div>
            @empty
                <p class="text-gray-500">No upcoming events in this type yet.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>