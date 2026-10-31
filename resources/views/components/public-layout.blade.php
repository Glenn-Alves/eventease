@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name', 'EventEase') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-100 text-gray-800 antialiased" style="font-family: 'DM Sans', sans-serif;">

    <header class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ route('home') }}" class="text-xl font-bold text-slate-900">EventEase</a>

            <div class="flex items-center gap-3 text-sm">
                @auth
                    <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-gray-800 text-white rounded-md">My dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2 border border-gray-800 rounded-md">Log in</a>
                    <a href="{{ route('register') }}" class="px-4 py-2 bg-gray-800 text-white rounded-md">Register</a>
                @endauth
            </div>
        </div>
    </header>

    <main>
        {{ $slot }}
    </main>

    <footer class="py-8 text-center text-xs text-gray-400">
        &copy; {{ date('Y') }} EventEase. All rights reserved.
    </footer>
</body>
</html>