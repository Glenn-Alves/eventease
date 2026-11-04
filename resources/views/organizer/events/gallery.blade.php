<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <h2 class="font-semibold text-xl text-gray-800">Gallery: {{ $event->title }}</h2>
            <div class="flex gap-4 text-sm">
                <a href="{{ route('events.show', $event) }}" target="_blank" class="underline text-gray-600">See the event page</a>
                <a href="{{ route('organizer.events.show', $event) }}" class="underline text-gray-600">Back to event</a>
            </div>
        </div>
    </x-slot>

    <datalist id="gallery-sections">
        <option value="Route maps">
        <option value="Featured artists">
        <option value="Sponsors">
        <option value="Photos">
    </datalist>

    <div class="py-12 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

        @if (session('status'))
            <div class="p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="p-3 bg-red-100 text-red-800 rounded text-sm">
                <ul class="list-disc ms-5">
                    @foreach (collect($errors->all())->unique() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Upload --}}
        <form method="POST" action="{{ route('organizer.events.gallery.store', $event) }}" enctype="multipart/form-data"
              class="bg-white p-6 rounded shadow space-y-4">
            @csrf

            <h3 class="font-semibold text-lg">Add images</h3>
            <p class="text-sm text-gray-600">
                Examples: a route map, featured artists, sponsor logos. Images in the same section are shown together on the event page.
            </p>

            <div>
                <x-input-label for="section" value="Section" />
                <x-text-input id="section" name="section" type="text" list="gallery-sections"
                              class="block mt-1 w-full md:w-1/2" :value="old('section', 'Route maps')" required />
            </div>

            <div>
                <x-input-label for="images" value="Images (JPG, PNG, or WEBP, up to 4 MB each, 12 at a time)" />
                <input id="images" name="images[]" type="file" multiple
                       accept="image/png,image/jpeg,image/webp" class="block mt-1 text-sm" required>
            </div>

            <x-primary-button>Upload</x-primary-button>
        </form>

        {{-- Images --}}
        <div class="bg-white p-6 rounded shadow"
             x-data="{
                saving: false,
                saved: false,
                order() { return [...this.$refs.list.querySelectorAll('[data-id]')].map(el => el.dataset.id); },
                async save() {
                    await this.$nextTick();
                    this.saving = true;
                    this.saved = false;

                    try {
                        await fetch(@js(route('organizer.events.gallery.reorder', $event)), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': @js(csrf_token()),
                            },
                            body: JSON.stringify({ order: this.order() }),
                        });
                        this.saved = true;
                    } catch (error) {}

                    this.saving = false;
                },
             }">

            <div class="flex items-center justify-between gap-3">
                <h3 class="font-semibold text-lg">Gallery ({{ $items->count() }})</h3>
                <span class="text-sm text-gray-500" x-show="saving" style="display: none;">Saving order...</span>
                <span class="text-sm text-green-700" x-show="saved" style="display: none;">Order saved</span>
            </div>

            <p class="mt-1 text-sm text-gray-600">
                Drag the grip to change the order. Sections appear on the event page in the order of their first image.
            </p>

            @if ($items->isEmpty())
                <p class="mt-6 text-sm text-gray-500">No images yet. Add your first image above.</p>
            @endif

            <div x-ref="list" x-sort="save()" class="mt-4 grid gap-4 sm:grid-cols-2">
                @foreach ($items as $item)
                    <div x-sort:item="{{ $item->id }}" data-id="{{ $item->id }}" class="rounded-lg border bg-white">

                        <div class="flex items-center gap-2 border-b bg-gray-50 px-3 py-1.5 text-xs text-gray-500">
                            <span x-sort:handle class="cursor-grab px-1" title="Drag to reorder">
                                <svg class="h-4 w-4" viewBox="0 0 16 16" fill="currentColor">
                                    <circle cx="5" cy="3" r="1.4"/><circle cx="11" cy="3" r="1.4"/>
                                    <circle cx="5" cy="8" r="1.4"/><circle cx="11" cy="8" r="1.4"/>
                                    <circle cx="5" cy="13" r="1.4"/><circle cx="11" cy="13" r="1.4"/>
                                </svg>
                            </span>
                            <span>{{ $item->section }}</span>
                        </div>

                        <div class="aspect-[4/3] bg-gray-100">
                            <img src="{{ $item->url() }}" alt="{{ $item->title ?: $item->section }}" loading="lazy"
                                 class="h-full w-full object-contain">
                        </div>

                        <form method="POST" action="{{ route('organizer.events.gallery.update', [$event, $item]) }}"
                              class="space-y-2 p-3">
                            @csrf
                            @method('PATCH')

                            <input type="text" name="section" list="gallery-sections" value="{{ $item->section }}" required
                                   class="block w-full border-gray-300 rounded-md shadow-sm text-sm">
                            <input type="text" name="title" value="{{ $item->title }}" placeholder="Title (optional)"
                                   class="block w-full border-gray-300 rounded-md shadow-sm text-sm">
                            <input type="text" name="caption" value="{{ $item->caption }}" placeholder="Caption (optional)"
                                   class="block w-full border-gray-300 rounded-md shadow-sm text-sm">

                            <button class="px-3 py-1 bg-gray-800 text-white text-xs rounded-md">Save</button>
                        </form>

                        <form method="POST" action="{{ route('organizer.events.gallery.destroy', [$event, $item]) }}"
                              onsubmit="return confirm('Delete this image?')" class="px-3 pb-3 text-right">
                            @csrf
                            @method('DELETE')
                            <button class="text-xs text-red-600 underline">Delete</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>