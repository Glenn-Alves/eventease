@php
    $cards = [
        [
            'icon' => 'user', 'label' => 'Accounts', 'value' => number_format($totalUsers),
            'hint' => ($users['organizer'] ?? 0).' organizers | '.($users['participant'] ?? 0).' participants | '.($users['admin'] ?? 0).' admins',
        ],
        [
            'icon' => 'calendar', 'label' => 'Events', 'value' => number_format($totalEvents),
            'hint' => $upcomingEvents.' upcoming',
        ],
        [
            'icon' => 'ticket', 'label' => 'Registrations', 'value' => number_format($totalRegistrations),
            'hint' => $checkedIn.' checked in',
        ],
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Admin dashboard</h2>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-10 sm:px-6 lg:px-8">
        <x-flash />

        <div class="flex flex-wrap gap-3">
            <x-action :href="route('admin.events.index')" icon="calendar" variant="primary">Manage events</x-action>
            <x-action :href="route('admin.users.index')" icon="user">Manage accounts</x-action>
            @can('manage-admins')
                <x-action :href="route('admin.admins.index')" icon="lock">Admin accounts</x-action>
            @endcan
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            @foreach ($cards as $card)
                <div class="rounded-xl bg-white p-5 shadow">
                    <div class="flex items-center gap-2 text-sm text-gray-700">
                        <x-icon :name="$card['icon']" class="h-5 w-5 text-gray-600" /> {{ $card['label'] }}
                    </div>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ $card['value'] }}</p>
                    <p class="mt-1 text-xs text-gray-700">{{ $card['hint'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-xl bg-white p-6 shadow">
                <h3 class="text-lg font-semibold text-gray-900">Newest events</h3>

                <ul class="mt-4 divide-y">
                    @forelse ($recentEvents as $event)
                        <li class="py-3">
                            <a href="{{ route('events.show', $event) }}" class="font-medium text-indigo-800 underline">{{ $event->title }}</a>
                            <p class="text-sm text-gray-700">
                                by {{ $event->organizer->name }} | {{ $event->event_date->format('M d, Y') }}
                                | {{ $event->registrations_count }} registered
                            </p>
                        </li>
                    @empty
                        <li class="py-3 text-sm text-gray-700">No events yet.</li>
                    @endforelse
                </ul>
            </section>

            <section class="rounded-xl bg-white p-6 shadow">
                <h3 class="text-lg font-semibold text-gray-900">Newest accounts</h3>

                <ul class="mt-4 divide-y">
                    @foreach ($recentUsers as $user)
                        <li class="flex flex-wrap items-center justify-between gap-2 py-3">
                            <div>
                                <p class="font-medium text-gray-900">{{ $user->name }}</p>
                                <p class="text-sm text-gray-700">{{ $user->email }} | joined {{ $user->created_at->format('M d, Y') }}</p>
                            </div>
                            <x-role-badge :role="$user->role" />
                        </li>
                    @endforeach
                </ul>
            </section>
        </div>
    </div>
</x-app-layout>