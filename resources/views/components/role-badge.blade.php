@props(['role'])

@php
    $map = [
        'admin' => ['blue', 'lock', 'Admin'],
        'organizer' => ['amber', 'calendar', 'Organizer'],
        'participant' => ['green', 'ticket', 'Participant'],
    ];

    [$tone, $icon, $label] = $map[$role] ?? ['gray', 'user', ucfirst($role)];
@endphp

<x-badge :tone="$tone" :icon="$icon">{{ $label }}</x-badge>