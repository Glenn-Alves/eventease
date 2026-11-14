@props(['status'])

@switch($status)
    @case('pending')
        <x-badge tone="amber" icon="clock">Waiting for approval</x-badge>
        @break

    @case('rejected')
        <x-badge tone="red" icon="x">Declined</x-badge>
        @break

    @default
        <x-badge tone="green" icon="check">Approved</x-badge>
@endswitch