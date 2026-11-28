@props(['title', 'sections' => []])

<x-public-layout :title="$title.' | EventEase'" hide-dashboard>
    <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
        <x-back-home />

        <article class="rounded-xl bg-white p-6 shadow sm:p-10">
            <h1 class="text-3xl font-bold text-slate-900">{{ $title }}</h1>
            <p class="mt-2 text-sm text-gray-700">Last updated: {{ config('legal.updated') }}</p>

            <nav aria-label="On this page" class="mt-6 rounded-lg bg-gray-50 p-4 text-sm">
                <p class="font-semibold text-slate-900">On this page</p>
                <ol class="mt-2 list-decimal space-y-1 ps-5">
                    @foreach ($sections as $id => $label)
                        <li><a href="#{{ $id }}" class="text-indigo-800 underline">{{ $label }}</a></li>
                    @endforeach
                </ol>
            </nav>

            <div class="mt-8 space-y-8 leading-relaxed text-gray-800
                        [&_h2]:mb-3 [&_h2]:scroll-mt-24 [&_h2]:text-xl [&_h2]:font-semibold [&_h2]:text-slate-900
                        [&_p+p]:mt-3 [&_ul]:mt-3 [&_ul]:list-disc [&_ul]:space-y-1 [&_ul]:ps-6
                        [&_a]:text-indigo-800 [&_a]:underline">
                {{ $slot }}
            </div>

            <p class="mt-10 border-t pt-6 text-sm text-gray-700">
                Questions? Write to {{ config('legal.contact_name') }} at
                <a href="mailto:{{ config('legal.contact_email') }}" class="text-indigo-800 underline">{{ config('legal.contact_email') }}</a>.
            </p>
        </article>
    </div>
</x-public-layout>