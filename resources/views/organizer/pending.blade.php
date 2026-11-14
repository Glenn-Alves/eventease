@php
    $user = auth()->user();
    $declined = $user->account_status === 'rejected';
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Organizer account</h2>
    </x-slot>

    <div class="mx-auto max-w-2xl space-y-6 px-4 py-10 sm:px-6 lg:px-8">
        <x-stepper :current="2" :steps="['Create account', 'Admin review', 'Create events']" />

        <div class="rounded-xl bg-white p-8 text-center shadow">
            <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full {{ $declined ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-800' }}">
                <x-icon :name="$declined ? 'warning' : 'clock'" class="h-7 w-7" />
            </span>

            <div class="mt-4 flex justify-center">
                <x-approval-badge :status="$user->account_status" />
            </div>

            @if ($declined)
                <h3 class="mt-4 text-xl font-semibold text-gray-900">Your organizer request was declined</h3>

                @if ($user->status_note)
                    <p class="mt-2 text-gray-800">Reason: {{ $user->status_note }}</p>
                @endif

                <p class="mt-2 text-sm text-gray-700">
                    You can still browse events. Contact the admin if you think this is a mistake.
                </p>
            @else
                <h3 class="mt-4 text-xl font-semibold text-gray-900">Your organizer account is waiting for approval</h3>
                <p class="mt-2 text-gray-800">
                    An admin reviews every new organizer account. You can create events as soon as it is approved.
                </p>
                <p class="mt-1 text-sm text-gray-700">Requested {{ $user->created_at->diffForHumans() }}.</p>
            @endif

            <div class="mt-6 flex flex-wrap justify-center gap-3">
                <x-action :href="route('organizer.pending')" icon="undo" variant="primary">Check status again</x-action>
                <x-action :href="route('home')" icon="search">Browse events</x-action>
            </div>

            <form method="POST" action="{{ route('logout') }}" class="mt-4">
                @csrf
                <button type="submit" class="tap text-sm text-gray-800 underline">Log out</button>
            </form>
        </div>
    </div>
</x-app-layout>