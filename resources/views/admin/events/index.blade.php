<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Manage events</h2>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-10 sm:px-6 lg:px-8">
        <x-flash />

        <form method="GET" action="{{ route('admin.events.index') }}"
              class="flex flex-wrap items-end gap-3 rounded-xl bg-white p-4 shadow">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-800">Search by event, organizer name, or email</label>
                <input id="search" name="search" type="search" value="{{ $search }}"
                       class="mt-1 w-80 max-w-full rounded-md border-gray-400 text-sm shadow-sm">
            </div>

            <x-action type="submit" icon="search" variant="primary">Search</x-action>

            @if ($search !== '')
                <x-action :href="route('admin.events.index')" icon="close">Clear</x-action>
            @endif
        </form>

        @if ($events->isEmpty())
            <x-empty-state icon="calendar" title="No events found" text="Try another search." />
        @else
            <div class="rounded-xl bg-white p-6 shadow">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[860px] text-left text-sm">
                        <thead class="text-gray-700">
                            <tr>
                                <th scope="col" class="py-2 pe-3">Event</th>
                                <th scope="col" class="py-2 pe-3">Organizer</th>
                                <th scope="col" class="py-2 pe-3">Date</th>
                                <th scope="col" class="py-2 pe-3">Registered</th>
                                <th scope="col" class="py-2 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($events as $event)
                                @php
                                    $confirmText = 'Delete the event "'.$event->title.'"? Its '.$event->registrations_count
                                        .' registration(s), form, and gallery will be deleted too. This cannot be undone.';
                                @endphp

                                <tr class="border-t align-top">
                                    <td class="py-3 pe-3">
                                        <div class="flex items-start gap-3">
                                            @if ($event->thumbnailUrl())
                                                <img src="{{ $event->thumbnailUrl() }}" alt="" class="h-12 w-20 rounded object-cover">
                                            @else
                                                <div class="flex h-12 w-20 items-center justify-center rounded bg-gray-100 text-gray-500">
                                                    <x-icon name="photo" class="h-5 w-5" />
                                                </div>
                                            @endif

                                            <div>
                                                <a href="{{ route('events.show', $event) }}" target="_blank"
                                                   class="font-medium text-indigo-800 underline">{{ $event->title }}</a>
                                                <div class="mt-1 flex flex-wrap gap-1.5">
                                                    <x-badge>{{ \App\Models\Event::TYPES[$event->type] ?? 'Others' }}</x-badge>
                                                    <x-event-status :event="$event" />
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="py-3 pe-3">
                                        <div class="font-medium text-gray-900">{{ $event->organizer->name }}</div>
                                        <div class="text-xs text-gray-700">{{ $event->organizer->email }}</div>
                                    </td>

                                    <td class="whitespace-nowrap py-3 pe-3">{{ $event->event_date->format('M d, Y') }}</td>

                                    <td class="py-3 pe-3">
                                        {{ $event->registrations_count }}
                                        @if ($event->registration_mode === 'builtin') of {{ $event->total_slots }} @endif
                                    </td>

                                    <td class="py-3 text-right">
                                        <form method="POST" action="{{ route('admin.events.destroy', $event) }}"
                                              onsubmit="return confirm(@js($confirmText))">
                                            @csrf
                                            @method('DELETE')
                                            <x-action type="submit" icon="trash" variant="danger">Delete</x-action>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $events->links() }}</div>
            </div>
        @endif
    </div>
</x-app-layout>