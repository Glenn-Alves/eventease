@props(['data', 'title' => 'Chart', 'unit' => 'registrations', 'height' => 240])

@php
    $points = collect($data)->values();
    $count = max($points->count(), 1);
    $max = max((int) $points->max('value'), 1);
    $niceTop = max(4, (int) ceil($max / 4) * 4);

    $w = 700;
    $left = 40;
    $right = 8;
    $top = 12;
    $bottom = 30;
    $plotW = $w - $left - $right;
    $plotH = $height - $top - $bottom;
    $slot = $plotW / $count;
    $barW = max(2, min(40, $slot * 0.7));
    $labelEvery = max(1, (int) ceil($count / 8));
    $peak = $points->sortByDesc('value')->first();
@endphp

<figure {{ $attributes }}>
    <div class="overflow-x-auto">
        <div class="min-w-[560px]">
            <svg viewBox="0 0 {{ $w }} {{ $height }}" class="h-auto w-full" role="img" aria-label="{{ $title }}">
                <title>{{ $title }}</title>

                @foreach ([0, 1, 2, 3, 4] as $i)
                    @php $y = $top + $plotH - ($plotH * $i / 4); @endphp
                    <line x1="{{ $left }}" x2="{{ $w - $right }}" y1="{{ round($y, 1) }}" y2="{{ round($y, 1) }}" stroke="#d1d5db" stroke-width="1" />
                    <text x="{{ $left - 6 }}" y="{{ round($y + 4, 1) }}" text-anchor="end" font-size="11" fill="#4b5563">{{ (int) ($niceTop * $i / 4) }}</text>
                @endforeach

                @foreach ($points as $i => $point)
                    @php
                        $barH = $plotH * $point['value'] / $niceTop;
                        $x = $left + $slot * $i + ($slot - $barW) / 2;
                        $y = $top + $plotH - $barH;
                    @endphp

                    <rect x="{{ round($x, 1) }}" y="{{ round($y, 1) }}" width="{{ round($barW, 1) }}"
                          height="{{ round($barH, 1) }}" rx="2" fill="#1e293b">
                        <title>{{ $point['label'] }}: {{ $point['value'] }} {{ $unit }}</title>
                    </rect>

                    @if ($i % $labelEvery === 0)
                        <text x="{{ round($left + $slot * $i + $slot / 2, 1) }}" y="{{ $height - 8 }}"
                              text-anchor="middle" font-size="11" fill="#4b5563">{{ $point['label'] }}</text>
                    @endif
                @endforeach
            </svg>
        </div>
    </div>

    @if ($peak && $peak['value'] > 0)
        <figcaption class="sr-only">Highest day: {{ $peak['value'] }} {{ $unit }} on {{ $peak['label'] }}.</figcaption>
    @endif
</figure>