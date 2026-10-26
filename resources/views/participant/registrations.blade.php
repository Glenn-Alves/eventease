<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">My Registrations</h2>
    </x-slot>
    <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
        @endif

        <div class="bg-white rounded shadow overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-3">Event</th>
                        <th class="p-3">Date</th>
                        <th class="p-3">Location</th>
                        <th class="p-3">Ticket Code</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($registrations as $registration)
                        <tr class="border-t">
                            <td class="p-3">{{ $registration->event->title }}</td>
                            <td class="p-3">{{ $registration->event->event_date->format('M d, Y') }}</td>
                            <td class="p-3">{{ $registration->event->location }}</td>
                            <td class="p-3 font-mono">{{ $registration->ticket_code }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="p-6 text-center text-gray-500">You have no registrations yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>