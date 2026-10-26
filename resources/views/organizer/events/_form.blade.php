@csrf

<div>
    <x-input-label for="title" value="Title" />
    <x-text-input id="title" name="title" type="text" class="block mt-1 w-full" :value="old('title', $event->title ?? '')" required />
    <x-input-error :messages="$errors->get('title')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="type" value="Event Type" />
    <select id="type" name="type" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
        @foreach (\App\Models\Event::TYPES as $value => $label)
            <option value="{{ $value }}" @selected(old('type', $event->type ?? 'others') === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('type')" class="mt-2" />
</div>

<div class="mt-4" x-data="{ preview: @js(isset($event) ? $event->thumbnailUrl() : null) }">
    <x-input-label for="thumbnail" value="Thumbnail (JPG, PNG, or WEBP, max 2 MB)" />

    <template x-if="preview">
        <img :src="preview" alt="Thumbnail preview" class="mt-2 w-full max-w-md aspect-video object-cover rounded border">
    </template>

    <input id="thumbnail" name="thumbnail" type="file" accept="image/png,image/jpeg,image/webp"
           class="block mt-2 text-sm"
           x-on:change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : preview">
    <x-input-error :messages="$errors->get('thumbnail')" class="mt-2" />

    @if (isset($event) && $event->thumbnail)
        <label class="flex items-center gap-2 mt-2 text-sm">
            <input type="checkbox" name="remove_thumbnail" value="1">
            Remove current thumbnail
        </label>
    @endif
</div>

<div class="mt-4">
    <x-input-label for="description" value="Description" />
    <textarea id="description" name="description" rows="4" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>{{ old('description', $event->description ?? '') }}</textarea>
    <x-input-error :messages="$errors->get('description')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="event_date" value="Date" />
    <x-text-input id="event_date" name="event_date" type="date" class="block mt-1 w-full" :value="old('event_date', isset($event) ? $event->event_date->format('Y-m-d') : '')" required />
    <x-input-error :messages="$errors->get('event_date')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="event_time" value="Time" />
    <x-text-input id="event_time" name="event_time" type="time" class="block mt-1 w-full" :value="old('event_time', isset($event) ? substr($event->event_time, 0, 5) : '')" required />
    <x-input-error :messages="$errors->get('event_time')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="location" value="Location" />
    <x-text-input id="location" name="location" type="text" class="block mt-1 w-full" :value="old('location', $event->location ?? '')" required />
    <x-input-error :messages="$errors->get('location')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="total_slots" value="Total Slots (for the whole event)" />
    <x-text-input id="total_slots" name="total_slots" type="number" min="1" class="block mt-1 w-full" :value="old('total_slots', $event->total_slots ?? '')" required />
    <x-input-error :messages="$errors->get('total_slots')" class="mt-2" />
</div>