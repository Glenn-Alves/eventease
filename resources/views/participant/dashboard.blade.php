<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Participant Dashboard</h2>
    </x-slot>
    <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 rounded shadow">
            Welcome, {{ auth()->user()->name }}! Browse events and register here.

            <div class="mt-4 flex gap-3">
                <a href="{{ route('participant.events.index') }}" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-md">Browse Events</a>
                <a href="{{ route('participant.registrations.index') }}" class="px-4 py-2 border border-gray-800 text-sm rounded-md">My Registrations</a>
            </div>
        </div>
    </div>
</x-app-layout>