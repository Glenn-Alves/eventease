<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Log in | {{ config('app.name', 'EventEase') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center bg-gray-100 px-4 py-10"
      style="font-family: 'DM Sans', sans-serif;">

    <main class="w-full max-w-md">

        {{-- Login Card --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm px-7 py-10 sm:px-10 sm:py-10">

            {{-- Heading --}}
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-slate-900">
                    Welcome Back
                </h1>

                <p class="mt-2 text-sm text-gray-500">
                    Sign in to your EventEase account
                </p>
            </div>

            {{-- Session Status --}}
            <x-auth-session-status
                class="mb-4"
                :status="session('status')"
            />

            {{-- Google and Facebook Buttons --}}
            {{-- Disabled until social login is configured --}}
            <div class="grid grid-cols-2 gap-3 mb-6">

                {{-- Google --}}
                <button
                    type="button"
                    disabled
                    title="Google sign-in is not available yet"
                    aria-label="Google sign-in unavailable"
                    class="h-12 flex items-center justify-center gap-2
                           border border-gray-300 rounded-lg bg-white
                           opacity-60 cursor-not-allowed"
                >
                    <svg class="w-5 h-5" viewBox="0 0 48 48" aria-hidden="true">
                        <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                        <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                        <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                        <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                    </svg>

                    <span class="text-sm font-medium text-gray-700">
                        Google
                    </span>
                </button>

                {{-- Facebook --}}
                <button
                    type="button"
                    disabled
                    title="Facebook sign-in is not available yet"
                    aria-label="Facebook sign-in unavailable"
                    class="h-12 flex items-center justify-center gap-2
                           border border-gray-300 rounded-lg bg-white
                           opacity-60 cursor-not-allowed"
                >
                    <svg class="w-5 h-5" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="#1877F2" d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.413c0-3.026 1.792-4.697 4.533-4.697 1.312 0 2.686.236 2.686.236v2.971H15.83c-1.491 0-1.956.93-1.956 1.886v2.264h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073z"/>
                    </svg>

                    <span class="text-sm font-medium text-gray-700">
                        Facebook
                    </span>
                </button>

            </div>

            {{-- Divider --}}
            <div class="flex items-center gap-4 mb-6">
                <div class="flex-1 border-t border-gray-200"></div>

                <span class="text-xs text-gray-400 whitespace-nowrap">
                    or continue with email
                </span>

                <div class="flex-1 border-t border-gray-200"></div>
            </div>

            {{-- Login Form --}}
            <form method="POST" action="{{ route('login') }}" x-data="{ show: false }">
                @csrf

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-800 mb-2">
                        Email address
                    </label>

                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="Enter your email address"
                        required
                        autofocus
                        autocomplete="username"
                        class="w-full h-12 px-4 border border-gray-300 rounded-lg
                               text-sm text-gray-800 placeholder-gray-400
                               focus:border-slate-700 focus:ring-2 focus:ring-slate-200
                               outline-none transition"
                    >

                    <x-input-error :messages="$errors->get('email')" class="mt-2"/>
                </div>

                {{-- Password --}}
                <div class="mt-5">
                    <div class="flex items-center justify-between mb-2">
                        <label for="password" class="block text-sm font-medium text-slate-800">
                            Password
                        </label>

                        @if (Route::has('password.request'))
                            <a
                                href="{{ route('password.request') }}"
                                class="text-sm font-medium text-slate-700 hover:text-slate-900 hover:underline"
                            >
                                Forgot password?
                            </a>
                        @endif
                    </div>

                    <div class="relative">
                        <input
                            id="password"
                            :type="show ? 'text' : 'password'"
                            name="password"
                            placeholder="Enter your password"
                            required
                            autocomplete="current-password"
                            class="w-full h-12 px-4 pr-12 border border-gray-300 rounded-lg
                                   text-sm text-gray-800 placeholder-gray-400
                                   focus:border-slate-700 focus:ring-2 focus:ring-slate-200
                                   outline-none transition"
                        >

                        {{-- Show / Hide Password --}}
                        <button
                            type="button"
                            @click="show = !show"
                            :aria-label="show ? 'Hide password' : 'Show password'"
                            class="absolute inset-y-0 right-0 px-4 flex items-center
                                   text-gray-400 hover:text-slate-800 transition"
                        >
                            {{-- Eye Icon --}}
                            <svg
                                x-show="!show"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="w-5 h-5"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                            </svg>

                            {{-- Eye Off Icon --}}
                            <svg
                                x-show="show"
                                x-cloak
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="w-5 h-5"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.853-.395M6.228 6.228A10.45 10.45 0 0 1 12 4.5c4.756 0 8.774 3.162 10.066 7.5a10.523 10.523 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 4.015 4.015m4.514 4.514 3.016 3.016M14.757 14.757a3 3 0 0 1-4.243-4.243"/>
                            </svg>
                        </button>
                    </div>

                    <x-input-error :messages="$errors->get('password')" class="mt-2"/>
                </div>

                {{-- Remember Me --}}
                <div class="mt-5">
                    <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer">
                        <input
                            id="remember_me"
                            type="checkbox"
                            name="remember"
                            class="rounded border-gray-300 text-slate-800
                                   focus:ring-slate-700"
                        >

                        <span class="text-sm text-gray-600">
                            Remember me
                        </span>
                    </label>
                </div>

                {{-- Sign In Button --}}
                <button
                    type="submit"
                    class="w-full h-12 mt-6 bg-slate-800 hover:bg-slate-900
                           text-white text-sm font-semibold rounded-lg
                           transition duration-200 focus:outline-none
                           focus:ring-2 focus:ring-slate-400 focus:ring-offset-2"
                >
                    Sign In
                </button>

            </form>

            {{-- Register Link --}}
            <p class="mt-7 text-center text-sm text-gray-600">
                Don't have an account?

                <a
                    href="{{ route('register') }}"
                    class="font-semibold text-slate-800 hover:text-slate-600 hover:underline"
                >
                    Sign up
                </a>
            </p>

        </div>

        {{-- Footer --}}
        <p class="mt-6 text-center text-xs text-gray-500">
            &copy; {{ date('Y') }} EventEase. All rights reserved.
        </p>

    </main>

</body>
</html>
