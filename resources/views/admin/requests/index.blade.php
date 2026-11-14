<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Requests</h2>
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-8 px-4 py-10 sm:px-6 lg:px-8">
        <x-flash />

        @if ($errors->any())
            <div class="flex items-start gap-3 rounded-xl border border-red-700/40 bg-red-50 p-4 text-red-950" role="alert">
                <x-icon name="warning" class="mt-0.5 h-5 w-5 shrink-0" />
                <ul class="space-y-1 text-sm">
                    @foreach (collect($errors->all())->unique() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($pendingAccounts->isEmpty() && $pendingEvents->isEmpty())
            <x-empty-state icon="check" title="Nothing is waiting for your decision"
                           text="New organizer accounts and new events show up here." />
        @endif

        {{-- Organizer accounts --}}
        @if ($pendingAccounts->isNotEmpty())
            <section class="rounded-xl bg-white p-6 shadow">
                <h3 class="text-lg font-semibold text-gray-900">Organizer accounts waiting ({{ $pendingAccounts->count() }})</h3>

                <ul class="mt-2 divide-y">
                    @foreach ($pendingAccounts as $account)
                        <li class="flex flex-wrap items-center justify-between gap-4 py-4">
                            <div>
                                <p class="font-medium text-gray-900">{{ $account->name }}</p>
                                <p class="text-sm text-gray-700">
                                    {{ $account->email }} | requested {{ $account->created_at->format('M d, Y h:i A') }}
                                    ({{ $account->created_at->diffForHumans() }})
                                </p>
                            </div>

                            <x-decision-buttons :approve-url="route('admin.requests.accounts.approve', $account)"
                                                :decline-url="route('admin.requests.accounts.decline', $account)"
                                                status="pending" />
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        {{-- Events --}}
        @if ($pendingEvents->isNotEmpty())
            <section class="rounded-xl bg-white p-6 shadow">
                <h3 class="text-lg font-semibold text-gray-900">Events waiting ({{ $pendingEvents->count() }})</h3>

                <ul class="mt-2 divide-y">
                    @foreach ($pendingEvents as $event)
                        <li class="py-5">
                            <div class="flex flex-col gap-4 sm:flex-row">
                                @if ($event->thumbnailUrl())
                                    <img src="{{ $event->thumbnailUrl() }}" alt="Poster of {{ $event->title }}"
                                         class="h-28 w-48 shrink-0 rounded object-cover">
                                @else
                                    <div class="flex h-28 w-48 shrink-0 items-center justify-center rounded bg-gray-100 text-gray-500">
                                        <x-icon name="photo" class="h-8 w-8" />
                                        <span class="sr-only">No poster</span>
                                    </div>
                                @endif

                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <x-badge>{{ \App\Models\Event::TYPES[$event->type] ?? 'Others' }}</x-badge>
                                        <a href="{{ route('events.show', $event) }}" target="_blank" rel="noopener"
                                           class="font-semibold text-indigo-800 underline">{{ $event->title }}</a>
                                    </div>

                                    <p class="mt-1 text-sm text-gray-700">
                                        by {{ $event->organizer->name }} ({{ $event->organizer->email }})
                                    </p>

                                    <dl class="mt-2 grid gap-1 text-sm text-gray-800 sm:grid-cols-2">
                                        <div class="flex items-center gap-2">
                                            <x-icon name="calendar" class="h-4 w-4 text-gray-600" />
                                            {{ $event->event_date->format('M d, Y') }} at {{ \Carbon\Carbon::parse($event->event_time)->format('h:i A') }}
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <x-icon name="pin" class="h-4 w-4 text-gray-600" /> {{ $event->location }}
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <x-icon name="ticket" class="h-4 w-4 text-gray-600" /> {{ $event->total_slots }} slots
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <x-icon name="clock" class="h-4 w-4 text-gray-600" />
                                            Submitted {{ $event->created_at->diffForHumans() }}
                                        </div>
                                    </dl>

                                    <p class="mt-2 text-sm text-gray-700">{{ \Illuminate\Support\Str::limit($event->description, 220) }}</p>
                                </div>
                            </div>

                            <x-decision-buttons class="mt-4"
                                                :approve-url="route('admin.requests.events.approve', $event)"
                                                :decline-url="route('admin.requests.events.decline', $event)"
                                                status="pending" reason-required />
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        {{-- Declined, with a way to change your mind --}}
        @if ($declinedAccounts->isNotEmpty() || $declinedEvents->isNotEmpty())
            <details class="rounded-xl bg-white p-6 shadow">
                <summary class="cursor-pointer text-lg font-semibold text-gray-900">
                    Declined ({{ $declinedAccounts->count() + $declinedEvents->count() }})
                </summary>

                <ul class="mt-2 divide-y">
                    @foreach ($declinedAccounts as $account)
                        <li class="flex flex-wrap items-center justify-between gap-4 py-4">
                            <div>
                                <p class="font-medium text-gray-900">{{ $account->name }} <span class="text-sm font-normal text-gray-700">(organizer account)</span></p>
                                <p class="text-sm text-gray-700">
                                    {{ $account->email }}
                                    @if ($account->status_note) | Reason: {{ $account->status_note }} @endif
                                </p>
                            </div>

                            <x-decision-buttons :approve-url="route('admin.requests.accounts.approve', $account)"
                                                :decline-url="route('admin.requests.accounts.decline', $account)"
                                                status="rejected" />
                        </li>
                    @endforeach

                    @foreach ($declinedEvents as $event)
                        <li class="flex flex-wrap items-center justify-between gap-4 py-4">
                            <div>
                                <p class="font-medium text-gray-900">{{ $event->title }} <span class="text-sm font-normal text-gray-700">(event by {{ $event->organizer->name }})</span></p>
                                @if ($event->approval_note)
                                    <p class="text-sm text-gray-700">Reason: {{ $event->approval_note }}</p>
                                @endif
                            </div>

                            <x-decision-buttons :approve-url="route('admin.requests.events.approve', $event)"
                                                :decline-url="route('admin.requests.events.decline', $event)"
                                                status="rejected" />
                        </li>
                    @endforeach
                </ul>
            </details>
        @endif
    </div>
</x-app-layout>