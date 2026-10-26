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

            <div class="bg-white rounded shadow overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="p-3">Image</th>
                            <th class="p-3">Title</th>
                            <th class="p-3">Type</th>
                            <th class="p-3">Date</th>
                            <th class="p-3">Time</th>
                            <th class="p-3">Location</th>
                            <th class="p-3">Slots</th>
                            <th class="p-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($events as $event)
                            <tr class="border-t">
                                <td class="p-3">
                                    @if ($event->thumbnailUrl())
                                        <img src="{{ $event->thumbnailUrl() }}" alt="" class="w-20 aspect-video object-cover rounded">
                                    @else
                                        <div class="w-20 aspect-video bg-gray-200 rounded"></div>
                                    @endif
                                </td>
                                <td class="p-3">{{ $event->title }}</td>
                                <td class="p-3">{{ \App\Models\Event::TYPES[$event->type] ?? 'Others' }}</td>
                                <td class="p-3">{{ $event->event_date->format('M d, Y') }}</td>
                                <td class="p-3">{{ \Carbon\Carbon::parse($event->event_time)->format('h:i A') }}</td>
                                <td class="p-3">{{ $event->location }}</td>
                                <td class="p-3">{{ $event->total_slots }}</td>
                                <td class="p-3">
                                    <div class="flex gap-3">
                                        <a href="{{ route('organizer.events.form.edit', $event) }}" class="text-emerald-600 underline">Form</a>
                                        <a href="{{ route('organizer.events.edit', $event) }}" class="text-indigo-600 underline">Edit</a>
                                        <form method="POST" action="{{ route('organizer.events.destroy', $event) }}" onsubmit="return confirm('Delete this event?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-red-600 underline">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="p-6 text-center text-gray-500">No events here yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>