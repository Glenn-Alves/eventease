@props(['messages'])

@if ($messages)
    <ul role="alert" {{ $attributes->merge(['class' => 'space-y-1 text-sm text-red-700']) }}>
        @foreach ((array) $messages as $message)
            <li class="flex items-start gap-1.5">
                <x-icon name="warning" class="mt-0.5 h-4 w-4" />
                <span>{{ $message }}</span>
            </li>
        @endforeach
    </ul>
@endif