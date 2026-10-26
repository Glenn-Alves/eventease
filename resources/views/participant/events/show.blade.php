@php
    $preview = $preview ?? false;
    $theme = $event->theme();
    $left = $event->total_slots - $event->registrations_count;
    $isExternal = $event->registration_mode === 'external' && $event->external_form_url;
    $showForm = ! $registration && ! $isExternal && ($left > 0 || $preview);
    $thumbInDetails = $event->thumbnailUrl() && ! ($showForm && $theme['show_banner']);
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">{{ $event->title }}</h2>
    </x-slot>

    <style>
        .ee-form .ee-input { display: block; width: 100%; margin-top: .25rem; padding: .5rem .75rem; background: #fff; color: #111827; border: 1px solid #d1d5db; border-radius: var(--ee-radius); }
        .ee-form .ee-input:focus { outline: 2px solid var(--ee-primary); outline-offset: 0; border-color: var(--ee-primary); box-shadow: none; }
        .ee-form input[type=radio], .ee-form input[type=checkbox] { color: var(--ee-primary); accent-color: var(--ee-primary); }
        .ee-card { display: block; padding: 1rem; background: #fff; color: #111827; border: 1px solid #d1d5db; border-radius: var(--ee-radius); }
        .ee-card:has(input:checked) { border-color: var(--ee-primary); box-shadow: 0 0 0 2px var(--ee-primary); }
        .ee-chip { position: relative; cursor: pointer; }
        .ee-chip input { position: absolute; opacity: 0; pointer-events: none; }
        .ee-chip span { display: inline-block; padding: .25rem .75rem; font-size: .875rem; border: 1px solid var(--ee-primary); border-radius: var(--ee-radius); }
        .ee-chip input:checked + span { background: var(--ee-primary); color: var(--ee-primary-text); }
    </style>

    <div class="py-12 max-w-3xl mx-auto sm:px-6 lg:px-8">

        @if ($preview)
            <div class="mb-4 p-3 bg-yellow-100 text-yellow-800 rounded text-sm">
                Preview only. This is how participants see your form. Registering is disabled here.
                <a href="{{ route('organizer.events.form.edit', $event) }}" class="underline">Back to the designer</a>
            </div>
        @endif

        {{-- Event details --}}
        <div class="bg-white p-6 rounded shadow">
            @if ($thumbInDetails)
                <img src="{{ $event->thumbnailUrl() }}" alt="{{ $event->title }}" class="w-full aspect-video object-cover rounded mb-4">
            @endif

            <p class="text-gray-700 whitespace-pre-line">{{ $event->description }}</p>

            <dl class="mt-4 text-sm space-y-1">
                <div><strong>Date:</strong> {{ $event->event_date->format('F d, Y') }}</div>
                <div><strong>Time:</strong> {{ \Carbon\Carbon::parse($event->event_time)->format('h:i A') }}</div>
                <div><strong>Location:</strong> {{ $event->location }}</div>
                @unless ($isExternal)
                    <div><strong>Available slots:</strong> {{ max($left, 0) }} of {{ $event->total_slots }}</div>
                @endunless
            </dl>
        </div>

        @if ($registration)
            <div class="mt-6 bg-white p-6 rounded shadow text-green-700">
                You are registered{{ $registration->category ? ' in the '.$registration->category->name.' category' : '' }}.
                Your ticket code: <strong>{{ $registration->ticket_code }}</strong>
            </div>

        @elseif ($isExternal)
            <div class="mt-6 bg-white p-6 rounded shadow">
                <p class="text-gray-700">The organizer uses their own registration form for this event. Click the button to open it in a new tab.</p>
                <a href="{{ $event->external_form_url }}" target="_blank" rel="noopener noreferrer"
                   class="inline-block mt-4 px-5 py-2 bg-gray-800 text-white rounded-md">Open registration form</a>
            </div>

        @elseif (! $showForm)
            <div class="mt-6 bg-white p-6 rounded shadow text-red-600">This event is full.</div>

        @else
            {{-- Themed registration form --}}
            <div class="ee-form mt-6 p-4 sm:p-8"
                 style="{{ $event->themeStyle() }}; background: var(--ee-bg); color: var(--ee-text); font-family: var(--ee-font); border-radius: var(--ee-radius);">

                @if ($theme['show_banner'] && $event->thumbnailUrl())
                    <img src="{{ $event->thumbnailUrl() }}" alt="{{ $event->title }}"
                         class="w-full aspect-video object-cover mb-4" style="border-radius: var(--ee-radius);">
                @endif

                <form method="POST" action="{{ route('participant.events.register', $event) }}"
                      class="p-6 sm:p-8" style="background: var(--ee-card); border-radius: var(--ee-radius);">
                    @csrf

                    <h3 class="text-2xl font-bold">{{ $theme['title'] ?: 'Register for '.$event->title }}</h3>

                    @if ($theme['intro'])
                        <p class="mt-2 opacity-80 whitespace-pre-line">{{ $theme['intro'] }}</p>
                    @endif

                    <x-input-error :messages="$errors->get('register')" class="mt-4" />

                    <p class="mt-4 text-sm opacity-70">
                        Registering as <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->email }})
                    </p>

                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-5">

                        {{-- Categories --}}
                        @if ($event->categories->isNotEmpty())
                            <div class="md:col-span-2">
                                <div class="pb-2 border-b-2 font-semibold text-lg" style="border-color: var(--ee-primary);">Choose your category</div>

                                <div class="mt-3 space-y-3">
                                    @foreach ($event->categories as $category)
                                        @php
                                            $catLeft = $category->slots - $category->registrations_count;
                                            $closed = $category->isClosed();
                                            $disabled = $catLeft <= 0 || $closed;
                                        @endphp

                                        <label class="ee-card {{ $disabled ? 'opacity-50' : 'cursor-pointer' }}">
                                            <div class="flex items-start gap-3">
                                                <input type="radio" name="category_id" value="{{ $category->id }}" class="mt-1"
                                                       @checked((string) old('category_id') === (string) $category->id)
                                                       @disabled($disabled)>
                                                <div class="flex-1">
                                                    <div class="flex justify-between">
                                                        <span class="font-semibold">{{ $category->name }}</span>
                                                        <span class="font-semibold">PHP {{ number_format($category->price, 2) }}</span>
                                                    </div>

                                                    <div class="text-xs mt-1" style="color: #6b7280;">
                                                        @if ($closed)
                                                            <span style="color: #dc2626;">Registration closed</span>
                                                        @elseif ($catLeft <= 0)
                                                            <span style="color: #dc2626;">Full</span>
                                                        @else
                                                            {{ $catLeft }} slots left
                                                            @if ($category->registration_ends_at)
                                                                | Registration ends {{ $category->registration_ends_at->format('M d, Y') }}
                                                            @endif
                                                        @endif
                                                    </div>

                                                    @if (count($category->inclusionList()))
                                                        <ul class="list-disc ms-5 mt-2 text-xs" style="color: #6b7280;">
                                                            @foreach ($category->inclusionList() as $item)
                                                                <li>{{ $item }}</li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>

                                <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                            </div>
                        @endif

                        {{-- Blocks in the order the organizer set --}}
                        @foreach ($event->fields as $field)

                            @if ($field->type === 'section')
                                <div class="md:col-span-2 pt-4 pb-2 border-b-2" style="border-color: var(--ee-primary);">
                                    <h4 class="text-lg font-semibold">{{ $field->label }}</h4>
                                    @if ($field->description)
                                        <p class="text-sm opacity-70 whitespace-pre-line">{{ $field->description }}</p>
                                    @endif
                                </div>

                            @elseif ($field->type === 'note')
                                <div class="md:col-span-2 p-4 text-sm whitespace-pre-line"
                                     style="border-left: 4px solid var(--ee-primary); background: rgba(0,0,0,.04); border-radius: var(--ee-radius);">
                                    <strong>{{ $field->label }}</strong>
                                    @if ($field->description)
                                        <div class="mt-1">{{ $field->description }}</div>
                                    @endif
                                </div>

                            @else
                                @php
                                    $name = "answers[{$field->id}]";
                                    $old = old("answers.{$field->id}");
                                    $fieldErrors = array_merge(
                                        $errors->get("answers.{$field->id}"),
                                        collect($errors->get("answers.{$field->id}.*"))->flatten()->all()
                                    );
                                @endphp

                                <div class="{{ $field->width === 'half' ? '' : 'md:col-span-2' }}">
                                    <label class="block font-medium text-sm">
                                        {{ $field->label }}
                                        @if ($field->is_required)
                                            <span style="color: #dc2626;">*</span>
                                        @endif
                                    </label>

                                    @if ($field->description)
                                        <p class="text-xs opacity-70 mt-0.5 whitespace-pre-line">{{ $field->description }}</p>
                                    @endif

                                    @switch($field->type)
                                        @case('textarea')
                                            <textarea name="{{ $name }}" rows="3" class="ee-input">{{ $old }}</textarea>
                                            @break

                                        @case('number')
                                            <input type="number" name="{{ $name }}" value="{{ $old }}" class="ee-input">
                                            @break

                                        @case('date')
                                            <input type="date" name="{{ $name }}" value="{{ $old }}" class="ee-input">
                                            @break

                                        @case('dropdown')
                                            <select name="{{ $name }}" class="ee-input">
                                                <option value="">-- Select option --</option>
                                                @foreach ($field->options as $option)
                                                    <option value="{{ $option }}" @selected($old === $option)>{{ $option }}</option>
                                                @endforeach
                                            </select>
                                            @break

                                        @case('radio')
                                            @foreach ($field->options as $option)
                                                <label class="flex items-center gap-2 mt-1 text-sm">
                                                    <input type="radio" name="{{ $name }}" value="{{ $option }}" @checked($old === $option)>
                                                    {{ $option }}
                                                </label>
                                            @endforeach
                                            @break

                                        @case('checkbox')
                                            <div class="mt-1 flex flex-wrap gap-2">
                                                @foreach ($field->options as $option)
                                                    <label class="ee-chip">
                                                        <input type="checkbox" name="{{ $name }}[]" value="{{ $option }}"
                                                               @checked(is_array($old) && in_array($option, $old))>
                                                        <span>{{ $option }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                            @break

                                        @default
                                            <input type="text" name="{{ $name }}" value="{{ $old }}" class="ee-input">
                                    @endswitch

                                    <x-input-error :messages="$fieldErrors" class="mt-1" />
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <button type="submit" @disabled($preview) class="mt-8 px-6 py-3 font-semibold"
                            style="background: var(--ee-primary); color: var(--ee-primary-text); border-radius: var(--ee-radius);">
                        {{ $theme['button'] }}
                    </button>
                </form>
            </div>
        @endif
    </div>
</x-app-layout>