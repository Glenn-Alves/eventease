@props(['event'])

@php
    $taken = $event->registrations_count ?? 0;
    $left = $event->total_slots - $taken;
    $external = $event->registration_mode === 'external';
@endphp

@if (! $event->isRegistrationOpen())
    <x-badge tone="red" icon="lock">Registration closed</x-badge>
@elseif (! $external && $left <= 0)
    <x-badge tone="red" icon="warning">Full</x-badge>
@elseif ($external)
    <x-badge tone="blue" icon="external">Own form</x-badge>
@elseif ($left <= max(3, (int) ceil($event->total_slots * 0.1)))
    <x-badge tone="amber" icon="warning">Only {{ $left }} left</x-badge>
@else
    <x-badge tone="green" icon="check">{{ $left }} slots left</x-badge>
@endif