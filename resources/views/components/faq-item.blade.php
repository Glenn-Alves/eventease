@props(['question'])

@php($panel = 'faq-'.\Illuminate\Support\Str::random(8))

<div x-data="{ open: false }" class="border-b border-gray-200 last:border-b-0">
    <h3>
        <button type="button" x-on:click="open = ! open" x-bind:aria-expanded="open.toString()"
                aria-controls="{{ $panel }}"
                class="tap flex w-full items-center justify-between gap-4 py-4 text-left text-base font-semibold text-slate-900 hover:text-indigo-800">
            <span>{{ $question }}</span>
            <x-icon name="chevron" class="h-5 w-5 shrink-0 transition-transform duration-300"
                    x-bind:class="open ? 'rotate-180' : ''" />
        </button>
    </h3>

    <div id="{{ $panel }}" inert
         class="grid transition-[grid-template-rows] duration-300 ease-out"
         style="grid-template-rows: 0fr;"
         x-bind:style="open ? 'grid-template-rows: 1fr' : 'grid-template-rows: 0fr'"
         x-effect="open ? $el.removeAttribute('inert') : $el.setAttribute('inert', '')">

        <div class="min-h-0 overflow-hidden">
            <div class="space-y-3 pb-5 pe-8 leading-relaxed text-gray-800
                        [&_a]:text-indigo-800 [&_a]:underline
                        [&_ul]:list-disc [&_ul]:space-y-1 [&_ul]:ps-6
                        [&_ol]:list-decimal [&_ol]:space-y-1 [&_ol]:ps-6">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>