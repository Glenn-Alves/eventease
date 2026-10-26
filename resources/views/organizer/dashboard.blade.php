<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Organizer Dashboard</h2>
    </x-slot>
    <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 rounded shadow">
            Welcome, {{ auth()->user()->name }}! You can create and manage events here.

            <div>
                <a href="{{ route('organizer.events.index') }}" class="inline-block mt-4 px-4 py-2 bg-gray-800 text-white text-sm rounded-md">Manage Events</a>
            </div>
        </div>
    </div>
</x-app-layout>