
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Sign Up | {{ config('app.name', 'EventEase') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }

        body {
            font-family: 'DM Sans', sans-serif;
        }
    </style>
</head>

<body class="min-h-screen bg-gray-100 text-gray-800 antialiased">

    <main class="min-h-screen flex items-center justify-center px-4 py-10 sm:px-6">

        <div class="w-full max-w-lg">

            {{-- Brand --}}
            <div class="text-center mb-7">
                <a href="{{ url('/') }}"
                   class="inline-flex items-center justify-center mb-4"
                   aria-label="EventEase home">

                    <svg class="w-12 h-12 text-slate-800"
                         viewBox="0 0 48 48"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="1.6"
                         stroke-linecap="round"
                         stroke-linejoin="round"
                         aria-hidden="true">

                        <path d="M8 13 20 6 32 13 20 20 8 13Z"/>
                        <path d="M8 13V34L20 42V20"/>
                        <path d="M20 42 32 35V13"/>
                        <path d="M20 27 32 20"/>
                        <path d="M27 23 39 16 44 19V30L32 37"/>
                        <path d="M39 16V27"/>
                        <path d="M27 23V34"/>
                    </svg>

                </a>

                <h1 class="text-3xl font-bold tracking-tight text-slate-900">
                    Create your account
                </h1>

                <p class="mt-2 text-sm text-gray-500">
                    Join EventEase and start your next chapter.
                </p>
            </div>


            {{-- Registration Card --}}
            <section class="bg-white border border-gray-200 rounded-2xl shadow-sm px-6 py-8 sm:px-9 sm:py-9">

                {{-- Heading --}}
                <div class="mb-7">
                    <h2 class="text-xl font-semibold text-slate-900">
                        Get started
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Fill in your details to create your account.
                    </p>
                </div>


                {{-- Session Status --}}
                <x-auth-session-status
                    class="mb-4"
                    :status="session('status')"
                />

                {{-- Validation Errors --}}
                @if ($errors->any())
                    <div class="mb-5 rounded-lg border border-red-200 bg-red-50 p-4">
                        <p class="text-sm font-semibold text-red-800">
                            Please check the following:
                        </p>

                        <ul class="mt-2 list-disc pl-5 text-sm text-red-700 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif


                {{-- Registration Form --}}
                <form method="POST" action="{{ route('register') }}" class="space-y-5">
                    @csrf

                    {{-- Full Name --}}
                    <div>
                        <label for="name"
                               class="block text-sm font-semibold text-gray-700 mb-2">
                            Full name
                        </label>

                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            placeholder="Enter your full name"
                            required
                            autofocus
                            autocomplete="name"
                            class="block w-full h-12 px-4 rounded-lg border border-gray-300 bg-white text-sm text-gray-800 placeholder-gray-400 shadow-sm transition focus:border-slate-500 focus:ring-4 focus:ring-slate-100 focus:outline-none"
                        >

                        <x-input-error :messages="$errors->get('name')" class="mt-2"/>
                    </div>


                    {{-- Email --}}
                    <div>
                        <label for="email"
                               class="block text-sm font-semibold text-gray-700 mb-2">
                            Email address
                        </label>

                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="you@example.com"
                            required
                            autocomplete="username"
                            class="block w-full h-12 px-4 rounded-lg border border-gray-300 bg-white text-sm text-gray-800 placeholder-gray-400 shadow-sm transition focus:border-slate-500 focus:ring-4 focus:ring-slate-100 focus:outline-none"
                        >

                        <x-input-error :messages="$errors->get('email')" class="mt-2"/>
                    </div>


                    {{-- Account Type --}}
                    <div>
                        <label for="role"
                               class="block text-sm font-semibold text-gray-700 mb-2">
                            Account type
                        </label>

                        <div class="relative">
                            <select
                                id="role"
                                name="role"
                                required
                                class="block w-full h-12 appearance-none rounded-lg border border-gray-300 bg-white px-4 pr-10 text-sm text-gray-800 shadow-sm transition focus:border-slate-500 focus:ring-4 focus:ring-slate-100 focus:outline-none"
                            >
                                <option value="participant"
                                    @selected(old('role', 'participant') === 'participant')>
                                    Participant
                                </option>

                                <option value="organizer"
                                    @selected(old('role') === 'organizer')>
                                    Event Organizer
                                </option>
                            </select>

                            <svg
                                class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-500"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.8"
                                aria-hidden="true"
                            >
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      d="m6 9 6 6 6-6"/>
                            </svg>
                        </div>

                        <p class="mt-2 text-xs text-gray-500">
                            Choose whether you want to attend events or organize them.
                        </p>

                        <x-input-error :messages="$errors->get('role')" class="mt-2"/>
                    </div>


                    {{-- Password --}}
                    <div x-data="{ showPassword: false }">

                        <label for="password"
                               class="block text-sm font-semibold text-gray-700 mb-2">
                            Password
                        </label>

                        <div class="relative">
                            <input
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                name="password"
                                placeholder="Create a password"
                                required
                                autocomplete="new-password"
                                class="block w-full h-12 px-4 pr-12 rounded-lg border border-gray-300 bg-white text-sm text-gray-800 placeholder-gray-400 shadow-sm transition focus:border-slate-500 focus:ring-4 focus:ring-slate-100 focus:outline-none"
                            >

                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                :aria-label="showPassword ? 'Hide password' : 'Show password'"
                                class="absolute inset-y-0 right-0 flex items-center px-4 text-gray-400 hover:text-slate-800"
                            >
                                <svg x-show="!showPassword"
                                     xmlns="http://www.w3.org/2000/svg"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor"
                                     stroke-width="1.7"
                                     class="h-5 w-5"
                                     aria-hidden="true">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                </svg>

                                <svg x-show="showPassword"
                                     x-cloak
                                     xmlns="http://www.w3.org/2000/svg"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor"
                                     stroke-width="1.7"
                                     class="h-5 w-5"
                                     aria-hidden="true">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.853-.395M6.228 6.228A10.45 10.45 0 0 1 12 4.5c4.756 0 8.774 3.162 10.066 7.5a10.523 10.523 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 4.015 4.015m4.514 4.514 3.016 3.016M14.757 14.757a3 3 0 0 1-4.243-4.243"/>
                                </svg>
                            </button>
                        </div>

                        <x-input-error :messages="$errors->get('password')" class="mt-2"/>

                        <p class="mt-2 text-xs text-gray-500">
                            Use a strong password that you can remember.
                        </p>
                    </div>


                    {{-- Confirm Password --}}
                    <div x-data="{ showPassword: false }">

                        <label for="password_confirmation"
                               class="block text-sm font-semibold text-gray-700 mb-2">
                            Confirm password
                        </label>

                        <div class="relative">
                            <input
                                id="password_confirmation"
                                :type="showPassword ? 'text' : 'password'"
                                name="password_confirmation"
                                placeholder="Enter your password again"
                                required
                                autocomplete="new-password"
                                class="block w-full h-12 px-4 pr-12 rounded-lg border border-gray-300 bg-white text-sm text-gray-800 placeholder-gray-400 shadow-sm transition focus:border-slate-500 focus:ring-4 focus:ring-slate-100 focus:outline-none"
                            >

                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                :aria-label="showPassword ? 'Hide password' : 'Show password'"
                                class="absolute inset-y-0 right-0 flex items-center px-4 text-gray-400 hover:text-slate-800"
                            >
                                <svg x-show="!showPassword"
                                     xmlns="http://www.w3.org/2000/svg"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor"
                                     stroke-width="1.7"
                                     class="h-5 w-5"
                                     aria-hidden="true">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                </svg>

                                <svg x-show="showPassword"
                                     x-cloak
                                     xmlns="http://www.w3.org/2000/svg"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor"
                                     stroke-width="1.7"
                                     class="h-5 w-5"
                                     aria-hidden="true">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.853-.395M6.228 6.228A10.45 10.45 0 0 1 12 4.5c4.756 0 8.774 3.162 10.066 7.5a10.523 10.523 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 4.015 4.015m4.514 4.514 3.016 3.016M14.757 14.757a3 3 0 0 1-4.243-4.243"/>
                                </svg>
                            </button>
                        </div>

                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2"/>
                    </div>


                    {{-- Submit --}}
                    <div class="pt-2">
                        <button
                            type="submit"
                            class="flex w-full h-12 items-center justify-center rounded-lg bg-slate-800 px-4 text-sm font-semibold text-white shadow-sm transition duration-200 hover:bg-slate-700 focus:outline-none focus:ring-4 focus:ring-slate-200 active:scale-[0.99]"
                        >
                            Create Account

                            <svg class="ml-2 h-4 w-4"
                                 xmlns="http://www.w3.org/2000/svg"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor"
                                 stroke-width="2"
                                 aria-hidden="true">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      d="M5 12h14m-7-7 7 7-7 7"/>
                            </svg>
                        </button>
                    </div>

                </form>


                {{-- Login Link --}}
                <div class="mt-7 border-t border-gray-100 pt-6 text-center">

                    <p class="text-sm text-gray-600">
                        Already have an account?

                        <a href="{{ route('login') }}"
                           class="font-semibold text-slate-800 hover:text-slate-600 hover:underline">
                            Sign in
                        </a>
                    </p>

                </div>

            </section>


            {{-- Footer --}}
            <p class="mt-6 text-center text-xs text-gray-400">
                &copy; {{ date('Y') }} EventEase. All rights reserved.
            </p>

        </div>

    </main>

</body>
</html>
