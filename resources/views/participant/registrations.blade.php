<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <h2 class="font-semibold text-xl text-gray-800">My Registrations</h2>
            <a href="{{ route('participant.dashboard') }}"
               class="px-4 py-2 border border-gray-800 text-sm rounded-md">Browse events</a>
        </div>
    </x-slot>

    <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-10">

        @if (session('status'))
            <div class="p-4 bg-green-100 text-green-800 rounded-lg">
                <strong>{{ session('status') }}</strong> Your ticket is below.
            </div>
        @endif

        @if ($upcoming->isEmpty() && $past->isEmpty())
            <div class="bg-white rounded-xl shadow p-10 text-center">
                <p class="text-lg font-semibold text-gray-800">No registrations yet</p>
                <p class="mt-1 text-sm text-gray-500">When you register for an event, your ticket shows up here.</p>
                <a href="{{ route('participant.dashboard') }}"
                   class="inline-block mt-5 px-5 py-2 bg-gray-800 text-white text-sm rounded-md">Find an event</a>
            </div>
        @endif

        @foreach (['Upcoming' => $upcoming, 'Past events' => $past] as $heading => $group)
            @if ($group->isNotEmpty())
                <section>
                    <h3 class="font-semibold text-lg text-gray-800 mb-4">
                        {{ $heading }}
                        <span class="ms-1 text-sm font-normal text-gray-500">({{ $group->count() }})</span>
                    </h3>

                    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($group as $registration)
                            @php
                                $event = $registration->event;
                                $isPast = $event->event_date->isBefore(today());
                                $days = (int) today()->diffInDays($event->event_date, false);

                                if ($isPast) {
                                    $badge = ['Past', 'bg-gray-200 text-gray-700'];
                                } elseif ($days === 0) {
                                    $badge = ['Today', 'bg-amber-100 text-amber-800'];
                                } elseif ($days === 1) {
                                    $badge = ['Tomorrow', 'bg-green-100 text-green-800'];
                                } elseif ($days <= 30) {
                                    $badge = ['In '.$days.' days', 'bg-green-100 text-green-800'];
                                } elseif ($days < 365) {
                                    $months = max(1, (int) round($days / 30));
                                    $badge = ['In '.$months.' '.\Illuminate\Support\Str::plural('month', $months), 'bg-green-100 text-green-800'];
                                } else {
                                    $years = max(1, (int) round($days / 365));
                                    $badge = ['In '.$years.' '.\Illuminate\Support\Str::plural('year', $years), 'bg-green-100 text-green-800'];
                                }
                            @endphp

                            <div class="bg-white rounded-xl shadow overflow-hidden flex flex-col {{ $isPast ? 'opacity-75' : '' }}">

                                {{-- Image (nothing on top of it) --}}
                                @if ($event->thumbnailUrl())
                                    <img src="{{ $event->thumbnailUrl() }}" alt="{{ $event->title }}" class="w-full aspect-video object-cover">
                                @else
                                    <div class="w-full aspect-video bg-gray-200 flex items-center justify-center text-sm text-gray-500">No image</div>
                                @endif

                                {{-- Event info --}}
                                <div class="p-5">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-xs px-2 py-1 bg-gray-100 rounded">
                                            {{ \App\Models\Event::TYPES[$event->type] ?? 'Others' }}
                                        </span>
                                        <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $badge[1] }}">
                                            {{ $badge[0] }}
                                        </span>
                                    </div>

                                    <h4 class="font-semibold text-lg mt-2">{{ $event->title }}</h4>

                                    <p class="text-sm text-gray-600 mt-1">
                                        {{ $event->event_date->format('D, M d, Y') }} at
                                        {{ \Carbon\Carbon::parse($event->event_time)->format('h:i A') }}
                                    </p>
                                    <p class="text-sm text-gray-600">{{ $event->location }}</p>

                                    @if ($registration->category)
                                        <div class="mt-3 flex items-center justify-between text-sm bg-gray-50 rounded-lg px-3 py-2">
                                            <span class="font-medium">{{ $registration->category->name }}</span>
                                            <span class="text-gray-600">PHP {{ number_format($registration->category->price, 2) }}</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Ticket tear line --}}
                                <div class="relative border-t-2 border-dashed border-gray-300 mx-5">
                                    <span class="absolute -top-3 -left-8 w-6 h-6 rounded-full bg-gray-100"></span>
                                    <span class="absolute -top-3 -right-8 w-6 h-6 rounded-full bg-gray-100"></span>
                                </div>

                                {{-- Ticket --}}
                                <div class="p-5 flex flex-col flex-1" x-data="{ copied: false }">
                                    <p class="text-xs uppercase tracking-wide text-gray-500">Ticket code</p>

                                    <div class="mt-1 flex items-center justify-between gap-3">
                                        <p class="font-mono text-2xl font-bold tracking-widest text-gray-800">{{ $registration->ticket_code }}</p>

                                        <button type="button"
                                                x-on:click="navigator.clipboard.writeText(@js($registration->ticket_code)); copied = true; setTimeout(() => copied = false, 1500)"
                                                class="text-xs px-3 py-1 border border-gray-300 rounded-md hover:bg-gray-50"
                                                x-text="copied ? 'Copied' : 'Copy'"></button>
                                    </div>

                                    <p class="mt-2 text-xs text-gray-500">
                                        Registered {{ $registration->created_at->format('M d, Y') }}
                                    </p>

                                    <a href="{{ route('participant.events.show', $event) }}"
                                       class="mt-auto pt-4 block">
                                        <span class="block text-center px-3 py-2 bg-gray-800 text-white text-sm rounded-md">View event</span>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        @endforeach
    </div>
</x-app-layout>