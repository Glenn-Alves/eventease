@props(['icon' => 'inbox', 'title', 'text' => null])

<div {{ $attributes->merge(['class' => 'rounded-xl bg-white p-10 text-center shadow']) }}>
    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-indigo-50 text-indigo-700">
        <x-icon :name="$icon" class="h-7 w-7" />
    </span>

    <p class="mt-4 text-lg font-semibold text-gray-900">{{ $title }}</p>

    @if ($text)
        <p class="mt-1 text-sm text-gray-700">{{ $text }}</p>
    @endif

    @if ($slot->isNotEmpty())
        <div class="mt-5 flex justify-center">{{ $slot }}</div>
    @endif
</div>