<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Manage accounts</h2>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-10 sm:px-6 lg:px-8">
        <x-flash />

        <form method="GET" action="{{ route('admin.users.index') }}"
              class="flex flex-wrap items-end gap-3 rounded-xl bg-white p-4 shadow">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-800">Search by name or email</label>
                <input id="search" name="search" type="search" value="{{ $search }}"
                       class="mt-1 w-72 max-w-full rounded-md border-gray-400 text-sm shadow-sm">
            </div>

            <div>
                <label for="role" class="block text-sm font-medium text-gray-800">Account type</label>
                <select id="role" name="role" class="mt-1 rounded-md border-gray-400 text-sm shadow-sm">
                    <option value="">All</option>
                    @foreach (['organizer' => 'Organizers', 'participant' => 'Participants', 'admin' => 'Admins'] as $value => $label)
                        <option value="{{ $value }}" @selected($role === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <x-action type="submit" icon="search" variant="primary">Show accounts</x-action>

            @if ($search !== '' || $role)
                <x-action :href="route('admin.users.index')" icon="close">Clear</x-action>
            @endif
        </form>

        @if ($users->isEmpty())
            <x-empty-state icon="user" title="No accounts found" text="Try another search or account type." />
        @else
            <div class="rounded-xl bg-white p-6 shadow">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[780px] text-left text-sm">
                        <thead class="text-gray-700">
                            <tr>
                                <th scope="col" class="py-2 pe-3">Account</th>
                                <th scope="col" class="py-2 pe-3">Type</th>
                                <th scope="col" class="py-2 pe-3">Joined</th>
                                <th scope="col" class="py-2 pe-3">Activity</th>
                                <th scope="col" class="py-2 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                @php
                                    $confirmText = $user->role === 'organizer'
                                        ? 'Delete the account of '.$user->name.'? Their '.$user->events_count.' event(s) and all registrations for them will be deleted. This cannot be undone.'
                                        : 'Delete the account of '.$user->name.'? Their '.$user->registrations_count.' registration(s) will be deleted and the slots freed. This cannot be undone.';
                                @endphp

                                <tr class="border-t align-top">
                                    <td class="py-3 pe-3">
                                        <div class="font-medium text-gray-900">{{ $user->name }}</div>
                                        <div class="text-xs text-gray-700">{{ $user->email }}</div>
                                    </td>

                                    <td class="py-3 pe-3"><x-role-badge :role="$user->role" /></td>

                                    <td class="whitespace-nowrap py-3 pe-3">{{ $user->created_at->format('M d, Y') }}</td>

                                    <td class="py-3 pe-3 text-gray-800">
                                        @if ($user->role === 'organizer')
                                            {{ $user->events_count }} events
                                        @elseif ($user->role === 'participant')
                                            {{ $user->registrations_count }} registrations
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td class="py-3 text-right">
                                        @if ($user->id === auth()->id())
                                            <span class="text-xs text-gray-700">This is you</span>
                                        @elseif ($user->isAdmin())
                                            <span class="inline-flex items-center gap-1 text-xs text-gray-700">
                                                <x-icon name="lock" class="h-4 w-4" /> Managed in Admin accounts
                                            </span>
                                        @else
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                                  onsubmit="return confirm(@js($confirmText))">
                                                @csrf
                                                @method('DELETE')
                                                <x-action type="submit" icon="trash" variant="danger">Delete account</x-action>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $users->links() }}</div>
            </div>
        @endif
    </div>
</x-app-layout>