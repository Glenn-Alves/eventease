@props(['title' => null, 'hideDashboard' => false])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'EventEase') }}</title>

    @include('partials.favicon')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="flex min-h-screen flex-col bg-gray-100 font-sans text-gray-800 antialiased">

    {{-- Skip link for keyboard navigation --}}
    <a href="#main"
       class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:font-medium focus:shadow">
        Skip to content
    </a>

    <header class="border-b border-gray-200 bg-white">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">

            <a href="{{ route('home') }}" class="flex items-center gap-2">
                <x-application-logo class="block h-9 w-auto text-slate-900" />
                <span class="font-serif text-xl font-bold tracking-wide text-slate-900">
                    EventEase
                </span>
            </a>

            <div class="flex items-center gap-3 text-sm">
                @auth
                    @unless ($hideDashboard)
                        <a href="{{ route('dashboard') }}"
                        class="tap inline-flex items-center rounded-md bg-gray-800 px-4 text-white">
                            My dashboard
                        </a>
                    @endunless
                @else
                    <a href="{{ route('login') }}"
                       class="tap inline-flex items-center rounded-md border border-gray-800 px-4">
                        Log in
                    </a>

                    <a href="{{ route('register') }}"
                       class="tap inline-flex items-center rounded-md bg-gray-800 px-4 text-white">
                        Register
                    </a>
                @endauth
            </div>

        </div>
    </header>

    <main id="main" class="flex-1">
        {{ $slot }}
    </main>

    {{-- Reusable site footer --}}
    <x-site-footer />

    {{-- Cookie notice (shown once, then remembered in the browser) --}}
    <x-cookie-notice />

</body>
</html>