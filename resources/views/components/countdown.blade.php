@props(['to', 'label' => 'Starts in', 'doneText' => 'Started', 'overlay' => false])

@php
    $base = $overlay
        ? 'cursor-pointer select-none rounded-xl bg-slate-900/70 p-2.5 text-white shadow-lg backdrop-blur transition-opacity duration-300'
        : 'rounded-xl bg-slate-800 p-4 text-white';

    $row = $overlay ? 'mt-1.5 flex gap-2' : 'mt-2 flex gap-3';

    $box = $overlay
        ? 'min-w-[2.5rem] rounded-md bg-white/10 px-1.5 py-1 text-lg font-bold tabular-nums'
        : 'min-w-[3.25rem] rounded-lg bg-white/10 px-2 py-2 text-2xl font-bold tabular-nums';

    $unitSize = $overlay ? 'text-[9px]' : 'text-[10px]';
    $labelSize = $overlay ? 'text-[10px]' : 'text-xs';
@endphp

<div {{ $attributes->merge(['class' => $base]) }}
     x-data="{
        target: new Date(@js($to)).getTime(),
        now: Date.now(),
        faded: false,
        init() { setInterval(() => { this.now = Date.now(); }, 1000); },
        get left() { return Math.max(0, this.target - this.now); },
        get done() { return this.target - this.now <= 0; },
        get days() { return Math.floor(this.left / 86400000); },
        get hours() { return Math.floor((this.left % 86400000) / 3600000); },
        get minutes() { return Math.floor((this.left % 3600000) / 60000); },
        get seconds() { return Math.floor((this.left % 60000) / 1000); },
        pad(n) { return String(n).padStart(2, '0'); },
     }"
     @if ($overlay)
         x-on:click="faded = !faded"
         :class="faded ? 'opacity-10' : 'hover:opacity-10'"
     @endif>

    <p class="{{ $labelSize }} uppercase tracking-wide text-slate-300">{{ $label }}</p>

    <div x-show="!done" class="{{ $row }}">
        @foreach (['days' => 'Days', 'hours' => 'Hours', 'minutes' => 'Mins', 'seconds' => 'Secs'] as $unit => $name)
            <div class="text-center">
                <div class="{{ $box }}" x-text="pad({{ $unit }})"></div>
                <div class="mt-0.5 {{ $unitSize }} uppercase tracking-wide text-slate-300">{{ $name }}</div>
            </div>
        @endforeach
    </div>

    <p x-show="done" style="display: none;" class="mt-1.5 font-semibold">{{ $doneText }}</p>
</div>