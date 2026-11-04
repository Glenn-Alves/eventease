@props(['event'])

@php
    $sections = $event->galleryItems->groupBy('section');
    $ordered = $sections->flatten(1)->values();
    $indexOf = $ordered->pluck('id')->flip();

    $payload = $ordered->map(fn ($item) => [
        'url' => $item->url(),
        'section' => $item->section,
        'title' => $item->title,
        'caption' => $item->caption,
    ])->values();
@endphp

@if ($ordered->isNotEmpty())
    <div {{ $attributes->merge(['class' => 'rounded-xl bg-white p-6 shadow']) }}
         x-data="{
            items: @js($payload),
            open: false,
            index: 0,
            get current() { return this.items[this.index] || {}; },
            show(i) { this.index = i; this.open = true; },
            next() { this.index = (this.index + 1) % this.items.length; },
            prev() { this.index = (this.index - 1 + this.items.length) % this.items.length; },
         }"
         x-on:keydown.escape.window="open = false"
         x-on:keydown.arrow-right.window="open && next()"
         x-on:keydown.arrow-left.window="open && prev()">

        <h3 class="text-lg font-semibold text-slate-900">Gallery</h3>

        <div class="mt-4 space-y-6">
            @foreach ($sections as $name => $group)
                <section>
                    <h4 class="text-sm font-semibold uppercase tracking-wide text-gray-500">{{ $name }}</h4>

                    <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3">
                        @foreach ($group as $item)
                            <button type="button" x-on:click="show({{ $indexOf[$item->id] }})" class="group text-left">
                                <span class="block aspect-[4/3] overflow-hidden rounded-lg bg-gray-100">
                                    <img src="{{ $item->url() }}" alt="{{ $item->title ?: $name }}" loading="lazy"
                                         class="h-full w-full object-contain transition group-hover:scale-105">
                                </span>

                                @if ($item->title)
                                    <span class="mt-1 block text-sm font-medium text-gray-800">{{ $item->title }}</span>
                                @endif
                                @if ($item->caption)
                                    <span class="block text-xs text-gray-500">{{ $item->caption }}</span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>

        {{-- Full size view --}}
        <div x-show="open" style="display: none;"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/85 p-4"
             x-on:click="open = false">

            <button type="button" x-on:click.stop="open = false" aria-label="Close"
                    class="absolute right-4 top-3 text-4xl leading-none text-white">&times;</button>

            @if ($ordered->count() > 1)
                <button type="button" x-on:click.stop="prev()" aria-label="Previous image"
                        class="absolute left-3 top-1/2 -translate-y-1/2 rounded-full bg-white/15 px-3 py-2 text-3xl leading-none text-white">&lsaquo;</button>
                <button type="button" x-on:click.stop="next()" aria-label="Next image"
                        class="absolute right-3 top-1/2 -translate-y-1/2 rounded-full bg-white/15 px-3 py-2 text-3xl leading-none text-white">&rsaquo;</button>
            @endif

            <figure class="max-w-5xl" x-on:click.stop>
                <img :src="current.url" :alt="current.title || current.section"
                     class="mx-auto max-h-[78vh] max-w-full rounded-lg bg-white/5">
                <figcaption class="mt-3 text-center text-sm text-white">
                    <span class="block font-semibold" x-text="current.title"></span>
                    <span class="block text-white/80" x-text="current.caption"></span>
                    <span class="mt-1 block text-xs text-white/60"
                          x-text="current.section + ' (' + (index + 1) + ' of ' + items.length + ')'"></span>
                </figcaption>
            </figure>
        </div>
    </div>
@endif