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

            <x-flash class="mb-4" />

            {{-- Event details --}}
            <div class="bg-white rounded shadow overflow-hidden">
                
            
                @if ($event->thumbnailUrl())
                    <div style="position: relative; width: 100%; overflow: hidden;">

                        <img
                            src="{{ $event->thumbnailUrl() }}"
                            alt="{{ $event->title }}"
                            style="width: 100%; aspect-ratio: 16 / 9; object-fit: cover; display: block;"
                        >

                        <div
                            style="
                                position: absolute;
                                top: 10px;
                                right: 10px;
                                z-index: 999;
                                transform: scale(0.6);
                                transform-origin: top right;
                                transition: opacity 0.3s ease;
                            "
                            onmouseover="this.style.opacity='0.3'"
                            onmouseout="this.style.opacity='1'"
                        >
                            <x-countdown
                                :to="$event->startsAt()->toIso8601String()"
                                label="Starts in"
                                done-text="Started"
                            />
                        </div>

                    </div>  
                @else
                    <div class="p-6">
                        <x-countdown
                            :to="$event->startsAt()->toIso8601String()"
                            label="Event starts in"
                            done-text="The event has started"
                        />
                    </div>
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
                        <div>
                            <strong>Registration deadline:</strong>
                            @if ($event->registration_deadline)
                                {{ $event->registration_deadline->format('F d, Y h:i A') }}
                            @else
                                None (closes when the event starts)
                            @endif
                            | {{ $event->closesInLabel() }}
                        </div>
                    </dl>

                    

                    <div class="mt-6 flex flex-wrap items-center gap-3">
                        @unless ($external)
                            <x-action :href="route('organizer.events.checkin.show', $event)" icon="qr" variant="primary">Check-in</x-action>
                        @endunless

                        <x-action :href="route('organizer.events.form.edit', $event)" icon="document"
                                :variant="$external ? 'primary' : 'secondary'">Design registration form</x-action>
                        <x-action :href="route('organizer.events.gallery.edit', $event)" icon="photo">
                            Gallery ({{ $event->galleryItems->count() }})
                        </x-action>
                        <x-action :href="route('organizer.events.preview', $event)" icon="eye" target="_blank">Preview form</x-action>
                        <x-action :href="route('organizer.events.edit', $event)" icon="edit">Edit details</x-action>

                        <form method="POST" action="{{ route('organizer.events.destroy', $event) }}" class="sm:ms-auto"
                            onsubmit="return confirm('Delete this event? All registrations for it will be deleted too.')">
                            @csrf
                            @method('DELETE')
                            <x-action type="submit" icon="trash" variant="danger">Delete event</x-action>
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
               <div class="grid gap-4 sm:grid-cols-4">
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
                    <div class="bg-white p-5 rounded shadow">
                        <p class="text-sm text-gray-500">Checked in</p>
                        <p class="text-3xl font-bold">
                            {{ $event->registrations->whereNotNull('checked_in_at')->count() }}
                        </p>
                    </div>
                </div>

                {{-- Categories --}}
                @if ($event->supportsCategories() && $event->categories->isNotEmpty())
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
                    <div class="flex flex-wrap items-center justify-between gap-3 px-6 pt-6">
                        <h3 class="font-semibold text-lg">Registered participants</h3>

                        @if ($event->registrations->isNotEmpty())
                            <a href="{{ route('organizer.events.registrations.export', $event) }}"
                            class="px-3 py-1 border border-gray-800 text-sm rounded-md">Download CSV</a>
                        @endif
                    </div>

                    <table class="w-full text-left text-sm mt-3">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="p-3 ps-6">Name</th>
                                <th class="p-3">Email</th>
                                @if ($event->supportsCategories())
                                    <th class="p-3">Category</th>
                                @endif
                                <th class="p-3">Ticket code</th>
                                <th class="p-3">Registered on</th>
                                <th class="p-3 pe-6">Answers</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($event->registrations as $registration)
                                <tr class="border-t">
                                    <td class="p-3 ps-6">{{ $registration->user->name }}</td>
                                    <td class="p-3">{{ $registration->user->email }}</td>
                                    @if ($event->supportsCategories())
                                        <td class="p-3">{{ $registration->category->name ?? '-' }}</td>
                                    @endif
                                    <td class="p-3 font-mono">{{ $registration->ticket_code }}</td>
                                    <td class="p-3">{{ $registration->created_at->format('M d, Y h:i A') }}</td>
                                    <td class="p-3 pe-6">
                                        <a href="{{ route('organizer.events.registrations.show', [$event, $registration]) }}"
                                        class="text-indigo-600 underline whitespace-nowrap">View answers</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="{{ $event->supportsCategories() ? 6 : 5 }}" class="p-6 text-center text-gray-500">No one has registered yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </x-app-layout>