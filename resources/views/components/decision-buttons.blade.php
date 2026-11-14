@props(['approveUrl', 'declineUrl', 'status', 'reasonRequired' => false])

<div x-data="{ declining: false }" {{ $attributes->merge(['class' => 'flex flex-wrap items-start justify-end gap-2']) }}>
    @if ($status !== 'approved')
        <form method="POST" action="{{ $approveUrl }}">
            @csrf
            <x-action type="submit" icon="check" variant="primary">Approve</x-action>
        </form>
    @endif

    @if ($status !== 'rejected')
        <x-action type="button" icon="close" variant="danger" x-on:click="declining = !declining"
                  x-bind:aria-expanded="declining.toString()">Decline</x-action>

        <form method="POST" action="{{ $declineUrl }}" x-show="declining" style="display: none;"
              class="flex w-full flex-wrap items-center justify-end gap-2">
            @csrf
            <input name="reason" type="text" maxlength="300" @if ($reasonRequired) required @endif
                   aria-label="Reason for declining"
                   placeholder="{{ $reasonRequired ? 'Reason (the organizer will see it)' : 'Reason (optional)' }}"
                   class="w-full rounded-md border-gray-400 text-sm shadow-sm sm:w-72">
            <x-action type="submit" icon="x" variant="danger">Send decline</x-action>
        </form>
    @endif
</div>