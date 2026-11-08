@php
    $cards = [
        [
            'icon' => 'ticket',
            'label' => 'Registrations',
            'value' => number_format($totalRegistrations),
            'hint' => $newInPeriod.' new in the last '.$days.' days',
        ],
        [
            'icon' => 'check',
            'label' => 'Checked in',
            'value' => number_format($checkedIn),
            'hint' => $attendanceRate !== null ? $attendanceRate.'% of registered people' : 'No registrations yet',
        ],
        [
            'icon' => 'chart',
            'label' => 'Slots filled',
            'value' => $fillRate !== null ? $fillRate.'%' : '-',
            'hint' => $slots > 0 ? $registeredInSlots.' of '.$slots.' slots' : 'No slot limits in this view',
        ],
        [
            'icon' => 'document',
            'label' => 'Expected revenue',
            'value' => 'PHP '.number_format($revenue, 2),
            'hint' => 'From category prices. Payments are not recorded.',
        ],
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-xl font-semibold text-gray-800">Reports</h2>

            <div class="no-print flex flex-wrap gap-3">
                <x-action :href="route('organizer.reports.export', request()->query())" icon="download">Download CSV</x-action>
                <x-action icon="document" onclick="window.print()">Print or save as PDF</x-action>
            </div>
        </div>
    </x-slot>

    <style>
        @media print {
            nav, header, .no-print { display: none !important; }
            body { background: #fff !important; }
            .keep-together { break-inside: avoid; }
        }
    </style>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-10 sm:px-6 lg:px-8">

        @if ($events->isEmpty())
            <x-empty-state icon="chart" title="No reports yet"
                           text="Reports appear after you create an event and people register.">
                <x-action :href="route('organizer.events.create')" icon="plus" variant="primary">Create an event</x-action>
            </x-empty-state>
        @else

            {{-- Choose what to look at --}}
            <form method="GET" action="{{ route('organizer.reports.index') }}"
                  class="no-print flex flex-wrap items-end gap-4 rounded-xl bg-white p-4 shadow">
                <div>
                    <label for="event" class="block text-sm font-medium text-gray-800">Event</label>
                    <select id="event" name="event" class="mt-1 w-72 max-w-full rounded-md border-gray-400 text-sm shadow-sm">
                        <option value="">All my events</option>
                        @foreach ($events as $event)
                            <option value="{{ $event->id }}" @selected($selected && $selected->id === $event->id)>
                                {{ $event->title }} ({{ $event->event_date->format('M d, Y') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="days" class="block text-sm font-medium text-gray-800">Registrations chart</label>
                    <select id="days" name="days" class="mt-1 rounded-md border-gray-400 text-sm shadow-sm">
                        @foreach ([7 => 'Last 7 days', 30 => 'Last 30 days', 90 => 'Last 90 days'] as $value => $label)
                            <option value="{{ $value }}" @selected($days === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <x-action type="submit" icon="search" variant="primary">Show report</x-action>
            </form>

            @if ($selected)
                <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-indigo-700/30 bg-indigo-50 p-4 text-indigo-950">
                    <p class="flex items-center gap-2 text-sm">
                        <x-icon name="info" class="h-5 w-5" />
                        Showing the report for <strong>{{ $selected->title }}</strong> only.
                    </p>

                    <div class="no-print flex flex-wrap gap-2">
                        <x-action :href="route('organizer.events.show', $selected)" icon="eye">Open event</x-action>
                        <x-action :href="route('organizer.reports.index', ['days' => $days])" icon="close">Show all events</x-action>
                    </div>
                </div>
            @endif

            {{-- Numbers --}}
            <div class="keep-together grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($cards as $card)
                    <div class="rounded-xl bg-white p-5 shadow">
                        <div class="flex items-center gap-2 text-sm text-gray-700">
                            <x-icon :name="$card['icon']" class="h-5 w-5 text-gray-600" />
                            {{ $card['label'] }}
                        </div>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ $card['value'] }}</p>
                        <p class="mt-1 text-xs text-gray-700">{{ $card['hint'] }}</p>
                    </div>
                @endforeach
            </div>

            {{-- Registrations per day --}}
            <section class="keep-together rounded-xl bg-white p-6 shadow">
                <div class="flex flex-wrap items-baseline justify-between gap-2">
                    <h3 class="text-lg font-semibold text-gray-900">Registrations per day</h3>
                    <p class="text-sm text-gray-700">Last {{ $days }} days: <strong>{{ $newInPeriod }}</strong> in total</p>
                </div>

                <x-bar-chart class="mt-4" :data="$series" title="Registrations per day, last {{ $days }} days" />
            </section>

            <div class="keep-together grid gap-6 lg:grid-cols-2">

                {{-- Attendance --}}
                <section class="rounded-xl bg-white p-6 shadow">
                    <h3 class="text-lg font-semibold text-gray-900">Attendance</h3>
                    <p class="mt-1 text-sm text-gray-700">
                        Registered people who checked in. Use the Check-in page on the day of the event.
                    </p>

                    <x-donut class="mt-5" :value="$checkedIn" :total="$totalRegistrations"
                             label="Attendance" done-label="Checked in" todo-label="Not checked in" />
                </section>

                {{-- By category --}}
                <section class="rounded-xl bg-white p-6 shadow">
                    <h3 class="text-lg font-semibold text-gray-900">Registrations by category</h3>

                    <x-hbar-list class="mt-4" :items="$byCategory"
                                 empty="No categories in this view. Categories are used for Fun Run events." />
                </section>
            </div>

            @unless ($selected)
                <section class="keep-together rounded-xl bg-white p-6 shadow">
                    <h3 class="text-lg font-semibold text-gray-900">Registrations by event type</h3>

                    <x-hbar-list class="mt-4" :items="$byType" empty="No registrations yet." />
                </section>
            @endunless

            {{-- One row per event --}}
            <section class="rounded-xl bg-white p-6 shadow">
                <h3 class="text-lg font-semibold text-gray-900">Event summary</h3>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-[720px] text-left text-sm">
                        <thead class="text-gray-700">
                            <tr>
                                <th scope="col" class="py-2 pe-3">Event</th>
                                <th scope="col" class="py-2 pe-3">Date</th>
                                <th scope="col" class="py-2 pe-3">Status</th>
                                <th scope="col" class="py-2 pe-3">Registered</th>
                                <th scope="col" class="py-2 pe-3">Checked in</th>
                                <th scope="col" class="py-2 pe-3">Expected revenue</th>
                                <th scope="col" class="no-print py-2"><span class="sr-only">Open</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($events as $event)
                                @php
                                    $external = $event->registration_mode === 'external';
                                    $registered = $event->registrations_count;
                                    $fill = (! $external && $event->total_slots > 0)
                                        ? min(100, (int) round($registered / $event->total_slots * 100))
                                        : null;
                                    $rate = $registered > 0 ? (int) round($event->checked_in_count / $registered * 100) : null;
                                    $state = $event->event_date->isToday() ? 'today' : ($event->event_date->isPast() ? 'past' : 'upcoming');
                                @endphp

                                <tr class="border-t align-top {{ $selected && $selected->id === $event->id ? 'bg-indigo-50' : '' }}">
                                    <td class="py-3 pe-3">
                                        <a href="{{ route('organizer.reports.index', ['event' => $event->id, 'days' => $days]) }}"
                                           class="font-medium text-indigo-800 underline">{{ $event->title }}</a>
                                        <div class="text-xs text-gray-700">{{ \App\Models\Event::TYPES[$event->type] ?? 'Others' }}</div>
                                    </td>

                                    <td class="whitespace-nowrap py-3 pe-3">{{ $event->event_date->format('M d, Y') }}</td>

                                    <td class="py-3 pe-3">
                                        @if ($state === 'today')
                                            <x-badge tone="amber" icon="clock">Today</x-badge>
                                        @elseif ($state === 'past')
                                            <x-badge tone="gray" icon="check">Past</x-badge>
                                        @else
                                            <x-badge tone="blue" icon="calendar">Upcoming</x-badge>
                                        @endif
                                    </td>

                                    <td class="py-3 pe-3">
                                        @if ($external)
                                            <span class="text-gray-700">Own form (not counted)</span>
                                        @else
                                            <div class="font-medium">{{ $registered }} of {{ $event->total_slots }}
                                                @if ($fill !== null)<span class="font-normal text-gray-700">({{ $fill }}%)</span>@endif
                                            </div>
                                            <div class="mt-1 h-2 w-32 rounded bg-gray-200" role="progressbar"
                                                 aria-label="Slots filled for {{ $event->title }}"
                                                 aria-valuenow="{{ $fill ?? 0 }}" aria-valuemin="0" aria-valuemax="100">
                                                <div class="h-2 rounded bg-slate-900" style="width: {{ $fill ?? 0 }}%"></div>
                                            </div>
                                        @endif
                                    </td>

                                    <td class="py-3 pe-3">
                                        @if ($external)
                                            <span class="text-gray-700">-</span>
                                        @else
                                            <span class="font-medium">{{ $event->checked_in_count }}</span>
                                            @if ($rate !== null)<span class="text-gray-700">({{ $rate }}%)</span>@endif
                                        @endif
                                    </td>

                                    <td class="whitespace-nowrap py-3 pe-3">PHP {{ number_format($event->revenue, 2) }}</td>

                                    <td class="no-print py-3 text-right">
                                        <a href="{{ route('organizer.events.show', $event) }}"
                                           class="tap inline-flex items-center gap-1 text-indigo-800 underline">
                                            Open <span class="sr-only">{{ $event->title }}</span>
                                            <x-icon name="next" class="h-4 w-4" />
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif
    </div>
</x-app-layout>