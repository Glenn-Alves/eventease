<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <h2 class="font-semibold text-xl text-gray-800">{{ $event->title }}</h2>
            <a href="{{ route('organizer.events.index') }}" class="text-sm underline text-gray-600">Back to events</a>
        </div>
    </x-slot>

    @php
        $external = $event->registration_mode === 'external';
        $left = max($event->total_slots - $event->registrations_count, 0);
    @endphp

    <div class="py-12 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

        @if (session('status'))
            <div class="p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
        @endif

        {{-- Event details --}}
        <div class="bg-white rounded shadow overflow-hidden">
            @if ($event->thumbnailUrl())
                <img src="{{ $event->thumbnailUrl() }}" alt="{{ $event->title }}" class="w-full max-h-80 object-cover">
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
                    <div>
                        <strong>Registration:</strong>
                        {{ $external ? 'Your own form link' : 'EventEase form' }}
                    </div>
                </dl>

                <div class="mt-6 flex flex-wrap gap-3 text-sm">
                    <a href="{{ route('organizer.events.form.edit', $event) }}"
                       class="px-4 py-2 bg-gray-800 text-white rounded-md">Design registration form</a>
                    <a href="{{ route('organizer.events.preview', $event) }}" target="_blank"
                       class="px-4 py-2 border border-gray-800 rounded-md">Preview form</a>
                    <a href="{{ route('organizer.events.edit', $event) }}"
                       class="px-4 py-2 border border-gray-800 rounded-md">Edit details</a>

                    <form method="POST" action="{{ route('organizer.events.destroy', $event) }}"
                          onsubmit="return confirm('Delete this event? All registrations for it will be deleted too.')">
                        @csrf
                        @method('DELETE')
                        <button class="px-4 py-2 border border-red-600 text-red-600 rounded-md">Delete</button>
                    </form>
                </div>
            </div>
        </div>

        @if ($external)
            <div class="bg-white p-6 rounded shadow text-sm text-gray-600">
                This event uses your own form link, so EventEase does not count or list the registrations.
            </div>
        @else
            {{-- Numbers --}}
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="bg-white p-5 rounded shadow">
                    <p class="text-sm text-gray-500">Registered</p>
                    <p class="text-3xl font-bold">{{ $event->registrations_count }}</p>
                </div>
                <div class="bg-white p-5 rounded shadow">
                    <p class="text-sm text-gray-500">Slots left</p>
                    <p class="text-3xl font-bold">{{ $left }}</p>
                </div>
                <div class="bg-white p-5 rounded shadow">
                    <p class="text-sm text-gray-500">Total slots</p>
                    <p class="text-3xl font-bold">{{ $event->total_slots }}</p>
                </div>
            </div>

            {{-- Categories --}}
            @if ($event->categories->isNotEmpty())
                <div class="bg-white rounded shadow overflow-x-auto">
                    <h3 class="font-semibold text-lg px-6 pt-6">Categories</h3>
                    <table class="w-full text-left text-sm mt-3">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="p-3 ps-6">Category</th>
                                <th class="p-3">Price</th>
                                <th class="p-3 pe-6">Registered</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($event->categories as $category)
                                <tr class="border-t">
                                    <td class="p-3 ps-6">{{ $category->name }}</td>
                                    <td class="p-3">PHP {{ number_format($category->price, 2) }}</td>
                                    <td class="p-3 pe-6">{{ $category->registrations_count }} of {{ $category->slots }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- Registered participants --}}
            <div class="bg-white rounded shadow overflow-x-auto">
                <h3 class="font-semibold text-lg px-6 pt-6">Registered participants</h3>
                <table class="w-full text-left text-sm mt-3">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="p-3 ps-6">Name</th>
                            <th class="p-3">Email</th>
                            <th class="p-3">Category</th>
                            <th class="p-3">Ticket code</th>
                            <th class="p-3 pe-6">Registered on</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($event->registrations as $registration)
                            <tr class="border-t">
                                <td class="p-3 ps-6">{{ $registration->user->name }}</td>
                                <td class="p-3">{{ $registration->user->email }}</td>
                                <td class="p-3">{{ $registration->category->name ?? '-' }}</td>
                                <td class="p-3 font-mono">{{ $registration->ticket_code }}</td>
                                <td class="p-3 pe-6">{{ $registration->created_at->format('M d, Y h:i A') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="p-6 text-center text-gray-500">No one has registered yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-app-layout>