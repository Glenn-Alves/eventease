@php
    $theme = $event->theme();
    $preview = $preview ?? false;
    $external = $event->registration_mode === 'external';
    $formTitle = $theme['title'] ?: 'Register for '.$event->title;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $formTitle }}</title>

    @if ($theme['font'] === 'rounded')
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700&display=swap" rel="stylesheet">
    @endif

    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; background: var(--ee-bg); color: var(--ee-text); font-family: var(--ee-font); line-height: 1.5; }
        .ee-page { max-width: 720px; margin: 0 auto; padding: 24px 16px 64px; }
        .ee-back { display: inline-block; margin-bottom: 12px; font-size: .875rem; color: inherit; opacity: .8; }
        .ee-preview { margin-bottom: 12px; padding: 10px 14px; font-size: .875rem; color: var(--ee-primary-text); background: var(--ee-primary); border-radius: var(--ee-radius); }
        .ee-preview a { margin-left: 8px; color: inherit; }
        .ee-banner { display: block; width: 100%; aspect-ratio: 16 / 9; object-fit: cover; margin-bottom: 16px; border-radius: var(--ee-radius); }
        .ee-card { padding: 28px; background: var(--ee-card); border-radius: var(--ee-radius); }
        .ee-title { margin: 0; font-size: 1.75rem; line-height: 1.2; }
        .ee-intro { margin: 8px 0 0; white-space: pre-line; opacity: .85; }
        .ee-as { margin: 14px 0 0; font-size: .85rem; opacity: .7; }
        .ee-alert { margin-top: 14px; padding: 10px 12px; font-size: .9rem; color: #b91c1c; border: 1px solid #b91c1c; border-radius: var(--ee-radius); }
        .ee-grid { display: grid; grid-template-columns: 1fr; gap: 20px; margin-top: 24px; }
        @media (min-width: 640px) { .ee-grid { grid-template-columns: 1fr 1fr; } }
        .ee-full { grid-column: 1 / -1; }
        .ee-section { padding: 8px 14px; font-weight: 700; color: var(--ee-primary-text); background: var(--ee-primary); border-radius: var(--ee-radius); }
        .ee-help { margin: 4px 0 0; font-size: .8rem; opacity: .7; white-space: pre-line; }
        .ee-note { padding: 12px 14px; font-size: .9rem; white-space: pre-line; background: rgba(0, 0, 0, .05); border-left: 4px solid var(--ee-primary); border-radius: var(--ee-radius); }
        .ee-label { display: block; font-size: .9rem; font-weight: 600; }
        .ee-req { color: var(--ee-primary); }
        .ee-input { display: block; width: 100%; margin-top: 6px; padding: 10px 12px; font: inherit; color: #111827; background: #fff; border: 1px solid rgba(0, 0, 0, .3); border-radius: var(--ee-radius); }
        .ee-input:focus { outline: 2px solid var(--ee-primary); outline-offset: 1px; }
        textarea.ee-input { min-height: 96px; resize: vertical; }
        .ee-opt { display: flex; align-items: center; gap: 8px; margin-top: 6px; font-size: .9rem; cursor: pointer; }
        .ee-opt input, .ee-cat input { width: 16px; height: 16px; margin: 0; accent-color: var(--ee-primary); }
        .ee-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px; }
        .ee-chip { position: relative; cursor: pointer; }
        .ee-chip input { position: absolute; width: 0; height: 0; opacity: 0; }
        .ee-chip span { display: inline-block; padding: 4px 14px; font-size: .875rem; border: 1px solid var(--ee-primary); border-radius: var(--ee-radius); }
        .ee-chip input:checked + span { color: var(--ee-primary-text); background: var(--ee-primary); }
        .ee-chip input:focus-visible + span { outline: 2px solid var(--ee-primary); outline-offset: 2px; }
        .ee-cat { display: flex; align-items: flex-start; gap: 12px; margin-top: 10px; padding: 14px; color: #111827; background: #fff; border: 1px solid rgba(0, 0, 0, .3); border-radius: var(--ee-radius); cursor: pointer; }
        .ee-cat:has(input:checked) { border-color: var(--ee-primary); box-shadow: 0 0 0 2px var(--ee-primary); }
        .ee-cat.is-disabled { opacity: .5; cursor: not-allowed; }
        .ee-cat-body { flex: 1; }
        .ee-cat-top { display: flex; justify-content: space-between; gap: 12px; }
        .ee-cat-sub { display: block; margin-top: 4px; font-size: .8rem; color: #6b7280; }
        .ee-incl { margin: 8px 0 0; padding-left: 18px; font-size: .8rem; color: #6b7280; }
        .ee-error { margin: 4px 0 0; font-size: .8rem; color: #b91c1c; }
        .ee-btn { display: inline-block; margin-top: 28px; padding: 12px 28px; font: inherit; font-weight: 600; text-decoration: none; color: var(--ee-primary-text); background: var(--ee-primary); border: 0; border-radius: var(--ee-radius); cursor: pointer; }
        .ee-btn:disabled { opacity: .5; cursor: not-allowed; }
    </style>
</head>
<body style="{{ $event->themeStyle() }}">

<div class="ee-page">

    @if ($preview)
        <div class="ee-preview">
            Preview only. Registering is disabled here.
            <a href="{{ route('organizer.events.form.edit', $event) }}">Back to the designer</a>
        </div>
    @else
        <a class="ee-back" href="{{ route('participant.events.show', $event) }}">&larr; Back to event</a>
    @endif

    @if ($external)
        <div class="ee-card">
            <h1 class="ee-title">{{ $event->title }}</h1>
            <p class="ee-intro">This event uses the organizer's own form.</p>
            @if ($event->external_form_url)
                <a class="ee-btn" href="{{ $event->external_form_url }}" target="_blank" rel="noopener noreferrer">Open the form</a>
            @endif
        </div>
    @else
        @if ($theme['show_banner'] && $event->thumbnailUrl())
            <img class="ee-banner" src="{{ $event->thumbnailUrl() }}" alt="{{ $event->title }}">
        @endif

        <form method="POST" action="{{ route('participant.events.register', $event) }}" class="ee-card">
            @csrf

            <h1 class="ee-title">{{ $formTitle }}</h1>

            @if ($theme['intro'])
                <p class="ee-intro">{{ $theme['intro'] }}</p>
            @endif

            @if ($errors->has('register'))
                <div class="ee-alert">{{ $errors->first('register') }}</div>
            @elseif ($errors->any())
                <div class="ee-alert">Please check the questions below.</div>
            @endif

            <p class="ee-as">
                @if ($preview)
                    Registering as <strong>Participant name</strong> (participant@email.com)
                @else
                    Registering as <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->email }})
                @endif
            </p>

            <div class="ee-grid">

                {{-- Categories --}}
                @if ($event->supportsCategories() && $event->categories->isNotEmpty())
                    <div class="ee-full">
                        <div class="ee-section">Choose your category</div>

                        @foreach ($event->categories as $category)
                            @php
                                $catLeft = $category->slots - $category->registrations_count;
                                $closed = $category->isClosed();
                                $disabled = $catLeft <= 0 || $closed;
                            @endphp

                            <label class="ee-cat {{ $disabled ? 'is-disabled' : '' }}">
                                <input type="radio" name="category_id" value="{{ $category->id }}"
                                       @checked((string) old('category_id') === (string) $category->id)
                                       @disabled($disabled)>
                                <span class="ee-cat-body">
                                    <span class="ee-cat-top">
                                        <strong>{{ $category->name }}</strong>
                                        <strong>PHP {{ number_format($category->price, 2) }}</strong>
                                    </span>
                                    <span class="ee-cat-sub">
                                        @if ($closed)
                                            Registration closed
                                        @elseif ($catLeft <= 0)
                                            Full
                                        @else
                                            {{ $catLeft }} slots left
                                            @if ($category->registration_ends_at)
                                                | Registration ends {{ $category->registration_ends_at->format('M d, Y') }}
                                            @endif
                                        @endif
                                    </span>
                                    @if (count($category->inclusionList()))
                                        <ul class="ee-incl">
                                            @foreach ($category->inclusionList() as $item)
                                                <li>{{ $item }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </span>
                            </label>
                        @endforeach

                        @if ($errors->has('category_id'))
                            <p class="ee-error">{{ $errors->first('category_id') }}</p>
                        @endif
                    </div>
                @endif

                {{-- Blocks in the order the organizer set --}}
                @foreach ($event->fields as $field)

                    @if ($field->type === 'section')
                        <div class="ee-full">
                            <div class="ee-section">{{ $field->label }}</div>
                            @if ($field->description)
                                <p class="ee-help">{{ $field->description }}</p>
                            @endif
                        </div>

                    @elseif ($field->type === 'note')
                        <div class="ee-full ee-note">
                            <strong>{{ $field->label }}</strong>
                            @if ($field->description)
                                <div>{{ $field->description }}</div>
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

                        <div class="{{ $field->width === 'half' ? '' : 'ee-full' }}">
                            <label class="ee-label" for="field-{{ $field->id }}">
                                {{ $field->label }}
                                @if ($field->is_required)
                                    <span class="ee-req">*</span>
                                @endif
                            </label>

                            @if ($field->description)
                                <p class="ee-help">{{ $field->description }}</p>
                            @endif

                            @switch($field->type)
                                @case('textarea')
                                    <textarea id="field-{{ $field->id }}" name="{{ $name }}" class="ee-input">{{ $old }}</textarea>
                                    @break

                                @case('number')
                                    <input id="field-{{ $field->id }}" type="number" name="{{ $name }}" value="{{ $old }}" class="ee-input">
                                    @break

                                @case('date')
                                    <input id="field-{{ $field->id }}" type="date" name="{{ $name }}" value="{{ $old }}" class="ee-input">
                                    @break

                                @case('dropdown')
                                    <select id="field-{{ $field->id }}" name="{{ $name }}" class="ee-input">
                                        <option value="">-- Select option --</option>
                                        @foreach ($field->options as $option)
                                            <option value="{{ $option }}" @selected($old === $option)>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                    @break

                                @case('radio')
                                    @foreach ($field->options as $option)
                                        <label class="ee-opt">
                                            <input type="radio" name="{{ $name }}" value="{{ $option }}" @checked($old === $option)>
                                            <span>{{ $option }}</span>
                                        </label>
                                    @endforeach
                                    @break

                                @case('checkbox')
                                    <div class="ee-chips">
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
                                    <input id="field-{{ $field->id }}" type="text" name="{{ $name }}" value="{{ $old }}" class="ee-input">
                            @endswitch

                            @foreach ($fieldErrors as $message)
                                <p class="ee-error">{{ $message }}</p>
                            @endforeach
                        </div>
                    @endif
                @endforeach
            </div>

            <button type="submit" class="ee-btn" @disabled($preview)>{{ $theme['button'] }}</button>
        </form>
    @endif
</div>

</body>
</html>