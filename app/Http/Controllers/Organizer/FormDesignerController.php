<?php

namespace App\Http\Controllers\Organizer;

use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventField;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FormDesignerController extends Controller
{
    private const BLOCK_TYPES = [
        'section', 'note', 'text', 'textarea', 'number', 'date', 'dropdown', 'radio', 'checkbox',
    ];

    public function edit(Event $event)
    {
        $this->checkOwner($event);
        $event->loadCount('registrations');

        return view('organizer.events.form', compact('event'));
    }

    // Shows the participant page exactly as participants will see it
    public function preview(Event $event)
    {
        $this->checkOwner($event);
        $event->loadCount('registrations');
        $event->load([
            'fields',
            'categories' => fn ($query) => $query->withCount('registrations'),
        ]);

        return view('participant.events.show', [
            'event' => $event,
            'registration' => null,
            'preview' => true,
        ]);
    }

    // Shows the participant page using the form as it is on screen now. Nothing is saved.
public function previewDraft(Request $request, Event $event)
{
    $this->checkOwner($event);

    $defaults = Event::THEME_DEFAULTS;
    $input = (array) $request->input('theme', []);

    $text = fn ($value, int $max) => is_string($value) ? Str::limit($value, $max, '') : '';

    $pickColor = fn (string $key) => (isset($input[$key]) && is_string($input[$key]) && preg_match('/^#[0-9a-fA-F]{6}$/', $input[$key]))
        ? $input[$key]
        : $defaults[$key];

    $font = is_string($input['font'] ?? null) ? $input['font'] : '';
    $radius = is_string($input['radius'] ?? null) ? $input['radius'] : '';

    $url = $text($request->input('external_form_url'), 500);

    // These changes stay in memory only. The model is never saved here.
    $event->registration_mode = $request->input('registration_mode') === 'external' ? 'external' : 'builtin';
    $event->external_form_url = Str::startsWith($url, 'https://') ? $url : null;
    $event->registrations_count = 0;
    $event->form_theme = [
        'primary' => $pickColor('primary'),
        'background' => $pickColor('background'),
        'card' => $pickColor('card'),
        'text' => $pickColor('text'),
        'font' => array_key_exists($font, Event::FONTS) ? $font : $defaults['font'],
        'radius' => array_key_exists($radius, Event::RADII) ? $radius : $defaults['radius'],
        'title' => $text($input['title'] ?? '', 120),
        'intro' => $text($input['intro'] ?? '', 1000),
        'button' => $text($input['button'] ?? '', 40) ?: $defaults['button'],
        'show_banner' => $request->boolean('theme.show_banner'),
    ];

    $blocks = collect((array) $request->input('blocks', []))
        ->sortKeys()
        ->filter(fn ($block) => is_array($block) && in_array($block['type'] ?? null, self::BLOCK_TYPES, true))
        ->values();

    $fields = $blocks->map(function ($block, $index) use ($text) {
        $type = $block['type'];
        $isInput = ! in_array($type, ['section', 'note']);

        $field = new EventField([
            'label' => $text($block['label'] ?? '', 255) ?: 'Untitled',
            'description' => $text($block['description'] ?? '', 2000) ?: null,
            'type' => $type,
            'width' => ($isInput && ($block['width'] ?? '') === 'half') ? 'half' : 'full',
            'options' => in_array($type, ['dropdown', 'radio', 'checkbox'])
                ? $this->previewOptions($block['options'] ?? '')
                : null,
            'is_required' => $isInput && ! empty($block['required']),
        ]);
        $field->id = -($index + 1);

        return $field;
    });

    $categories = collect((array) $request->input('categories', []))
        ->sortKeys()
        ->filter(fn ($item) => is_array($item))
        ->values()
        ->map(function ($item, $index) use ($text) {
            $endsAt = $item['ends_at'] ?? null;

            $category = new EventCategory([
                'name' => $text($item['name'] ?? '', 100) ?: 'Category',
                'price' => is_numeric($item['price'] ?? null) ? $item['price'] : 0,
                'slots' => (int) ($item['slots'] ?? 0) ?: 100,
                'inclusions' => $text($item['inclusions'] ?? '', 2000),
                'registration_ends_at' => (is_string($endsAt) && strtotime($endsAt)) ? $endsAt : null,
            ]);
            $category->id = -($index + 1);
            $category->registrations_count = 0;

            return $category;
        });

    $event->setRelation('fields', $fields);
    $event->setRelation('categories', $categories);

    return view('participant.events.show', [
        'event' => $event,
        'registration' => null,
        'preview' => true,
    ]);
}

private function previewOptions(mixed $text): array
{
    return collect(preg_split('/\r\n|\r|\n/', is_string($text) ? $text : ''))
        ->map(fn ($option) => trim($option))
        ->filter()
        ->values()
        ->all();
}

    public function update(Request $request, Event $event)
    {
        $this->checkOwner($event);

        $color = ['required', 'regex:/^#[0-9a-fA-F]{6}$/'];

        $rules = [
            'registration_mode' => ['required', Rule::in(['builtin', 'external'])],
            'external_form_url' => ['nullable', 'required_if:registration_mode,external', 'url:https', 'max:500'],

            'theme' => ['required', 'array'],
            'theme.primary' => $color,
            'theme.background' => $color,
            'theme.card' => $color,
            'theme.text' => $color,
            'theme.font' => ['required', Rule::in(array_keys(Event::FONTS))],
            'theme.radius' => ['required', Rule::in(array_keys(Event::RADII))],
            'theme.title' => ['nullable', 'string', 'max:120'],
            'theme.intro' => ['nullable', 'string', 'max:1000'],
            'theme.button' => ['required', 'string', 'max:40'],
        ];

        // Categories and blocks only matter when EventEase hosts the form
        if ($request->input('registration_mode') === 'builtin') {
            $rules += [
                'categories' => ['nullable', 'array', 'max:10'],
                'categories.*.id' => ['nullable', 'integer'],
                'categories.*.name' => ['required', 'string', 'max:100'],
                'categories.*.price' => ['required', 'numeric', 'min:0'],
                'categories.*.slots' => ['required', 'integer', 'min:1'],
                'categories.*.inclusions' => ['nullable', 'string'],
                'categories.*.ends_at' => ['nullable', 'date'],

                'blocks' => ['nullable', 'array', 'max:80'],
                'blocks.*.id' => ['nullable', 'integer'],
                'blocks.*.type' => ['required', Rule::in(self::BLOCK_TYPES)],
                'blocks.*.label' => ['required', 'string', 'max:255'],
                'blocks.*.description' => ['nullable', 'string', 'max:2000'],
                'blocks.*.options' => ['nullable', 'string', 'required_if:blocks.*.type,dropdown,radio,checkbox'],
                'blocks.*.required' => ['nullable', 'boolean'],
                'blocks.*.width' => ['nullable', Rule::in(['full', 'half'])],
            ];
        }

        $data = $request->validate($rules, [
            'external_form_url.required_if' => 'Paste the link of your form.',
            'external_form_url.url' => 'The form link must be a valid link that starts with https://',
            'categories.*.name.required' => 'Every category needs a name.',
            'categories.*.price.required' => 'Every category needs a price (use 0 for free).',
            'categories.*.slots.required' => 'Every category needs a number of slots.',
            'blocks.*.label.required' => 'Every block needs a title or question.',
            'blocks.*.options.required_if' => 'Add at least one option for dropdown, multiple choice, and checkbox questions.',
        ]);

        $theme = $data['theme'];

        $event->update([
            'registration_mode' => $data['registration_mode'],
            'external_form_url' => $data['external_form_url'] ?? null,
            'form_theme' => [
                'primary' => $theme['primary'],
                'background' => $theme['background'],
                'card' => $theme['card'],
                'text' => $theme['text'],
                'font' => $theme['font'],
                'radius' => $theme['radius'],
                'title' => $theme['title'] ?? '',
                'intro' => $theme['intro'] ?? '',
                'button' => $theme['button'],
                'show_banner' => $request->boolean('theme.show_banner'),
            ],
        ]);

        if ($data['registration_mode'] === 'builtin') {
            $this->syncCategories($event, $data['categories'] ?? []);
            $this->syncBlocks($event, $data['blocks'] ?? []);
        }

        return redirect()->route('organizer.events.form.edit', $event)
            ->with('status', 'Registration form saved.');
    }

    private function checkOwner(Event $event): void
    {
        abort_unless($event->user_id === auth()->id(), 403);
    }

    private function syncCategories(Event $event, array $categories): void
    {
        ksort($categories);
        $keepIds = [];

        foreach (array_values($categories) as $position => $item) {
            $attributes = [
                'name' => $item['name'],
                'price' => $item['price'],
                'slots' => $item['slots'],
                'inclusions' => $item['inclusions'] ?? null,
                'registration_ends_at' => $item['ends_at'] ?? null,
                'position' => $position,
            ];

            $category = ! empty($item['id'])
                ? EventCategory::where('event_id', $event->id)->find($item['id'])
                : null;

            if ($category) {
                $category->update($attributes);
            } else {
                $category = $event->categories()->create($attributes);
            }

            $keepIds[] = $category->id;
        }

        // Categories that already have registrations are never deleted
        EventCategory::where('event_id', $event->id)
            ->whereNotIn('id', $keepIds)
            ->doesntHave('registrations')
            ->delete();
    }

    private function syncBlocks(Event $event, array $blocks): void
    {
        ksort($blocks);
        $keepIds = [];

        foreach (array_values($blocks) as $position => $item) {
            $type = $item['type'];
            $isInput = ! in_array($type, ['section', 'note']);

            $options = null;

            if (in_array($type, ['dropdown', 'radio', 'checkbox'])) {
                $options = collect(preg_split('/\r\n|\r|\n/', $item['options'] ?? ''))
                    ->map(fn ($option) => trim($option))
                    ->filter()
                    ->values()
                    ->all();
            }

            $attributes = [
                'label' => $item['label'],
                'description' => $item['description'] ?? null,
                'type' => $type,
                'width' => $isInput ? ($item['width'] ?? 'full') : 'full',
                'options' => $options,
                'is_required' => $isInput && ! empty($item['required']),
                'position' => $position,
            ];

            $field = ! empty($item['id'])
                ? EventField::where('event_id', $event->id)->find($item['id'])
                : null;

            if ($field) {
                $field->update($attributes);
            } else {
                $field = $event->fields()->create($attributes);
            }

            $keepIds[] = $field->id;
        }

        EventField::where('event_id', $event->id)->whereNotIn('id', $keepIds)->delete();
    }
}