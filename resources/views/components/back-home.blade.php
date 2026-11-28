@auth
    <x-back-link :href="route('dashboard')">Back to dashboard</x-back-link>
@else
    <x-back-link :href="route('home')">Back to events</x-back-link>
@endauth