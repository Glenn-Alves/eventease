@props(['src', 'alt' => ''])

<div x-data="{ loaded: false }" {{ $attributes->merge(['class' => 'relative overflow-hidden bg-gray-100']) }}>
    <div x-show="! loaded" class="skeleton absolute inset-0 rounded-none" aria-hidden="true"></div>

    <img src="{{ $src }}" alt="{{ $alt }}" loading="lazy" decoding="async"
         x-on:load="loaded = true" x-on:error="loaded = true"
         x-init="if ($el.complete) loaded = true"
         class="relative h-full w-full object-cover transition-opacity duration-300"
         x-bind:class="loaded ? 'opacity-100' : 'opacity-0'">
</div>