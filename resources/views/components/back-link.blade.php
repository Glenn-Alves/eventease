@props(['href'])

@once
    <style>
        .back-link { display: inline-flex; align-items: center; min-height: 44px; margin-bottom: 16px; padding: 0 18px; font-size: .9rem; font-weight: 600; line-height: 1.25; color: #0f172a; text-decoration: none; background: #fff; border: 1px solid rgba(0, 0, 0, .15); border-radius: 8px; transition: background-color .15s, color .15s, border-color .15s; }
        .back-link:hover { color: #fff; background: #0f172a; border-color: #0f172a; }
        .back-link:focus-visible { outline: 3px solid #4f46e5; outline-offset: 2px; }
        @media (prefers-reduced-motion: reduce) { .back-link { transition: none; } }
    </style>
@endonce

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'back-link']) }}>{{ $slot }}</a>