<x-error-page code="404" title="Page not found" icon="search" tone="blue"
              message="This page does not exist, was moved, or is not public yet. If you followed a link to an event, the organizer may have removed it.">
    <x-action :href="route('home')" icon="calendar" variant="primary">Browse events</x-action>
    <x-action type="button" icon="back" onclick="history.back()">Go back</x-action>

    @auth
        <x-action :href="route('dashboard')" icon="home">My dashboard</x-action>
    @endauth
</x-error-page>