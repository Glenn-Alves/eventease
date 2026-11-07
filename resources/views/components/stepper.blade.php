@props(['current' => 1, 'steps' => ['Choose event', 'Fill the form', 'Get your ticket']])

<nav aria-label="Registration steps" {{ $attributes }}>
    <p class="mb-2 text-xs font-medium text-gray-700">Step {{ $current }} of {{ count($steps) }}</p>

    <ol class="flex items-center gap-2">
        @foreach ($steps as $index => $name)
            @php
                $number = $index + 1;
                $state = $number < $current ? 'done' : ($number === $current ? 'current' : 'todo');
            @endphp

            <li class="flex items-center gap-2" @if ($state === 'current') aria-current="step" @endif>
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-semibold
                    {{ $state === 'done' ? 'bg-green-700 text-white' : ($state === 'current' ? 'bg-slate-900 text-white' : 'bg-white text-gray-700 ring-1 ring-gray-400') }}">
                    @if ($state === 'done')
                        <x-icon name="tick" class="h-4 w-4" />
                        <span class="sr-only">Completed:</span>
                    @else
                        {{ $number }}
                    @endif
                </span>

                <span class="{{ $state === 'current' ? 'text-sm font-semibold text-gray-900' : 'hidden text-sm text-gray-700 sm:inline' }}">{{ $name }}</span>

                @unless ($loop->last)
                    <span class="h-px w-6 bg-gray-400 sm:w-10" aria-hidden="true"></span>
                @endunless
            </li>
        @endforeach
    </ol>
</nav>