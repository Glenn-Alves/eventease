<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Edit Event</h2>
    </x-slot>
    <div class="py-12 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('organizer.events.update', $event) }}" enctype="multipart/form-data" class="bg-white p-6 rounded shadow">
            @method('PUT')
            @include('organizer.events._form')
            <div class="mt-6">
                <x-primary-button>Update Event</x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>