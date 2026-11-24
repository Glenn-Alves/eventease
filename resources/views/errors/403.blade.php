@php
    $detail = trim((string) $exception->getMessage());

    if ($detail === '' || $detail === 'This action is unauthorized.') {
        $detail = 'Your account type cannot open this page.';
    }
@endphp

<x-error-page code="403" title="You do not have access" icon="lock" tone="red" :message="$detail">
    @auth
        <x-action :href="route('dashboard')" icon="home" variant="primary">Go to my dashboard</x-action>
    @else
        <x-action :href="route('login')" icon="lock" variant="primary">Log in</x-action>
    @endauth

    <x-action :href="route('home')" icon="calendar">Browse events</x-action>
</x-error-page>