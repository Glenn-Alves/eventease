<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Admin accounts</h2>
    </x-slot>

    <div class="mx-auto max-w-4xl space-y-6 px-4 py-10 sm:px-6 lg:px-8">
        <x-flash />

        <section class="rounded-xl bg-white p-6 shadow">
            <h3 class="text-lg font-semibold text-gray-900">Current admins</h3>

            <ul class="mt-4 divide-y">
                @foreach ($admins as $admin)
                    @php
                        $confirmText = 'Remove admin access from '.$admin->name.'? The account becomes a normal participant account.';
                    @endphp

                    <li class="flex flex-wrap items-center justify-between gap-3 py-3">
                        <div>
                            <p class="flex flex-wrap items-center gap-2 font-medium text-gray-900">
                                {{ $admin->name }}
                                @if ($admin->id === $ownerId)
                                    <x-badge tone="blue" icon="lock">Owner</x-badge>
                                @endif
                                @if ($admin->id === auth()->id())
                                    <span class="text-xs font-normal text-gray-700">(you)</span>
                                @endif
                            </p>
                            <p class="text-sm text-gray-700">{{ $admin->email }} | added {{ $admin->created_at->format('M d, Y') }}</p>
                        </div>

                        @if ($admin->id !== $ownerId && $admin->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.admins.destroy', $admin) }}"
                                  onsubmit="return confirm(@js($confirmText))">
                                @csrf
                                @method('DELETE')
                                <x-action type="submit" icon="close" variant="danger">Remove admin access</x-action>
                            </form>
                        @endif
                    </li>
                @endforeach
            </ul>
        </section>

        <section class="rounded-xl bg-white p-6 shadow">
            <h3 class="text-lg font-semibold text-gray-900">Create an admin account</h3>
            <p class="mt-1 text-sm text-gray-700">
                Admins can delete any event and any organizer or participant account. Only you, the owner,
                can create or remove admins. Give each new admin a strong password.
            </p>

            <form method="POST" action="{{ route('admin.admins.store') }}" class="mt-5 space-y-4">
                @csrf

                <div>
                    <x-input-label for="name" value="Full name" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="email" value="Email" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required autocomplete="off" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="password" value="Password" />
                    <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="password_confirmation" value="Confirm the password" />
                    <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                </div>

                <x-action type="submit" icon="plus" variant="primary">Create admin account</x-action>
            </form>
        </section>
    </div>
</x-app-layout>