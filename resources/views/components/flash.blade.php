@if (session('status'))
    <div role="status"
         {{ $attributes->merge(['class' => 'flex items-start gap-3 rounded-lg border border-green-700/40 bg-green-50 p-4 text-green-900']) }}
         x-data="{ show: true }"
         x-show="show"
         x-init="setTimeout(() => show = false, 8000)">

        <x-icon name="tick" class="mt-0.5 h-5 w-5 text-green-700" />

        <p class="flex-1 text-sm font-medium">
            @if (session('status') === 'verification-link-sent')
                A new verification link has been sent to the email address you provided during registration.
            @else
                {{ session('status') }}
            @endif
        </p>

        <button type="button"
                x-on:click="show = false"
                class="tap -m-2 inline-flex items-center justify-center rounded p-2 hover:bg-green-100">
            <x-icon name="close" class="h-4 w-4" />
            <span class="sr-only">Dismiss message</span>
        </button>
    </div>
@endif