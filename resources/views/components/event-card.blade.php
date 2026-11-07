@props(['event', 'href', 'registered' => false, 'organizer' => false])

@php
    $taken = $event->registrations_count ?? 0;
    $percent = $event->total_slots > 0 ? min(100, (int) round($taken / $event->total_slots * 100)) : 0;
@endphp

<article class="flex flex-col overflow-hidden rounded-xl bg-white shadow">
    @if ($event->thumbnailUrl())
        <img src="{{ $event->thumbnailUrl() }}" alt="Poster of {{ $event->title }}" loading="lazy"
             class="aspect-video w-full object-cover">
    @else
        <div class="flex aspect-video w-full items-center justify-center bg-gray-100 text-gray-500">
            <x-icon name="photo" class="h-10 w-10" />
            <span class="sr-only">No image</span>
        </div>
    @endif

    <div class="flex flex-1 flex-col p-5">
        <div class="flex flex-wrap items-center gap-2">
            <x-badge>{{ \App\Models\Event::TYPES[$event->type] ?? 'Others' }}</x-badge>

            @if ($registered)
                <x-badge tone="green" icon="check">Registered</x-badge>
            @endif

            @unless ($organizer)
                <x-event-status :event="$event" />
            @endunless
        </div>

        <h3 class="mt-3 text-lg font-semibold text-gray-900">{{ $event->title }}</h3>

        <dl class="mt-2 space-y-1.5 text-sm text-gray-700">
            <div class="flex items-center gap-2">
                <x-icon name="calendar" class="h-4 w-4 text-gray-500" />
                <dt class="sr-only">Date</dt>
                <dd>{{ $event->event_date->format('M d, Y') }} at {{ \Carbon\Carbon::parse($event->event_time)->format('h:i A') }}</dd>
            </div>
            <div class="flex items-center gap-2">
                <x-icon name="pin" class="h-4 w-4 text-gray-500" />
                <dt class="sr-only">Location</dt>
                <dd class="truncate">{{ $event->location }}</dd>
            </div>
            <div class="flex items-center gap-2">
                <x-icon name="clock" class="h-4 w-4 text-indigo-700" />
                <dt class="sr-only">Countdown</dt>
                <dd class="font-medium text-indigo-800">{{ $event->startsInLabel() }}</dd>
            </div>
        </dl>

        @if ($organizer)
            <div class="mt-3">
                @if ($event->registration_mode === 'external')
                    <p class="flex items-center gap-1.5 text-sm text-gray-700">
                        <x-icon name="external" class="h-4 w-4" /> Uses an external form
                    </p>
                @else
                    <div class="flex justify-between text-xs text-gray-700">
                        <span>{{ $taken }} of {{ $event->total_slots }} registered</span>
                        <span>{{ $percent }}%</span>
                    </div>
                    <div class="mt-1 h-2 rounded bg-gray-200" role="progressbar" aria-label="Registered participants"
                         aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100">
                        <div class="h-2 rounded bg-slate-900" style="width: {{ $percent }}%"></div>
                    </div>
                @endif
            </div>
        @elseif ($event->registration_deadline && $event->isRegistrationOpen())
            <p class="mt-2 flex items-center gap-1.5 text-xs text-gray-700">
                <x-icon name="lock" class="h-3.5 w-3.5" /> {{ $event->closesInLabel() }}
            </p>
        @endif

        <div class="mt-auto pt-4">
            <x-action :href="$href" icon="next" trailing variant="primary" class="w-full">
                {{ $organizer ? 'Manage event' : 'View event' }}
            </x-action>
        </div>
    </div>
</article>