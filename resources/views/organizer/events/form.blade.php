@php
    $initialCategories = array_values(old('categories', $event->categories()->withCount('registrations')->get()->map(fn ($c) => [
        'id' => $c->id,
        'name' => $c->name,
        'price' => $c->price,
        'slots' => $c->slots,
        'inclusions' => $c->inclusions,
        'ends_at' => optional($c->registration_ends_at)->format('Y-m-d'),
        'locked' => $c->registrations_count > 0,
    ])->all()));

    $initialBlocks = array_values(old('blocks', $event->fields->map(fn ($f) => [
        'id' => $f->id,
        'type' => $f->type,
        'label' => $f->label,
        'description' => $f->description,
        'options' => implode("\n", $f->options ?? []),
        'required' => $f->is_required,
        'width' => $f->width,
    ])->all()));

    $initialTheme = old('theme', $event->theme());
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <h2 class="font-semibold text-xl text-gray-800">Registration Form: {{ $event->title }}</h2>
            <a href="{{ route('organizer.events.index') }}" class="underline text-sm text-gray-600">Back to events</a>
        </div>
    </x-slot>

    <div class="py-12 max-w-4xl mx-auto sm:px-6 lg:px-8">

        @if (session('status'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded text-sm">
                <ul class="list-disc ms-5">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('organizer.events.form.update', $event) }}" x-data="formDesigner" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- 1. How do people register? --}}
            <div class="bg-white p-6 rounded shadow">
                <h3 class="font-semibold text-lg">How do people register?</h3>

                <label class="flex items-start gap-3 mt-4 cursor-pointer">
                    <input type="radio" name="registration_mode" value="builtin" x-model="mode" class="mt-1">
                    <span>
                        <strong>Use the EventEase form</strong><br>
                        <span class="text-sm text-gray-600">You design the form below. EventEase counts the slots, saves the answers, and makes a ticket for each participant.</span>
                    </span>
                </label>

                <label class="flex items-start gap-3 mt-4 cursor-pointer">
                    <input type="radio" name="registration_mode" value="external" x-model="mode" class="mt-1">
                    <span>
                        <strong>Use my own form link</strong> (Google Forms or any other form)<br>
                        <span class="text-sm text-gray-600">Participants fill out your form on its own site. EventEase will not save the answers, count slots, or make tickets for this event.</span>
                    </span>
                </label>

                <div class="mt-4" x-show="mode === 'external'">
                    <x-input-label for="external_form_url" value="Form link (must start with https://)" />
                    <x-text-input id="external_form_url" name="external_form_url" type="url" class="block mt-1 w-full"
                                  placeholder="https://forms.gle/..." :value="old('external_form_url', $event->external_form_url)" />
                </div>
            </div>

            <div x-show="mode === 'builtin'" class="space-y-6">

                {{-- 2. Categories --}}
                <div class="bg-white p-6 rounded shadow">
                    <h3 class="font-semibold text-lg">Categories</h3>
                    <p class="text-sm text-gray-600">
                        Example: 42K, 21K, 10K, 5K. Each category has its own price and slots. Leave this empty if your event has no categories.
                        Set the event's Total Slots to the total of all category slots.
                    </p>

                    <template x-for="(cat, index) in categories" :key="index">
                        <div class="mt-4 p-4 border rounded bg-gray-50">
                            <input type="hidden" :name="`categories[${index}][id]`" :value="cat.id ?? ''">

                            <div class="grid gap-3 md:grid-cols-4">
                                <input type="text" :name="`categories[${index}][name]`" x-model="cat.name"
                                       placeholder="Name (e.g. 42K)"
                                       class="md:col-span-2 block w-full border-gray-300 rounded-md shadow-sm">
                                <input type="number" step="0.01" min="0" :name="`categories[${index}][price]`" x-model="cat.price"
                                       placeholder="Price (PHP)"
                                       class="block w-full border-gray-300 rounded-md shadow-sm">
                                <input type="number" min="1" :name="`categories[${index}][slots]`" x-model="cat.slots"
                                       placeholder="Slots"
                                       class="block w-full border-gray-300 rounded-md shadow-sm">
                            </div>

                            <div class="grid gap-3 md:grid-cols-2 mt-3">
                                <textarea :name="`categories[${index}][inclusions]`" x-model="cat.inclusions" rows="4"
                                          placeholder="Inclusions, one per line"
                                          class="block w-full border-gray-300 rounded-md shadow-sm text-sm"></textarea>
                                <div>
                                    <label class="text-sm text-gray-600">Registration ends (optional)</label>
                                    <input type="date" :name="`categories[${index}][ends_at]`" x-model="cat.ends_at"
                                           class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                </div>
                            </div>

                            <div class="mt-3 text-right text-sm">
                                <button type="button" x-show="!cat.locked" x-on:click="removeCategory(index)"
                                        class="text-red-600 underline">Remove</button>
                                <span x-show="cat.locked" class="text-gray-500">Has registrations, cannot be removed</span>
                            </div>
                        </div>
                    </template>

                    <button type="button" x-on:click="addCategory()"
                            class="mt-4 px-4 py-2 border border-gray-800 text-sm rounded-md">+ Add Category</button>
                </div>

                {{-- 3. Form blocks (drag and drop) --}}
                <div class="bg-white p-6 rounded shadow">
                    <h3 class="font-semibold text-lg">Form</h3>
                    <p class="text-sm text-gray-600">
                        Name and email come from the participant's account. Drag the grip on a block to change the order.
                    </p>

                    @if ($event->registrations_count > 0)
                        <p class="mt-3 text-sm text-amber-800 bg-amber-50 p-2 rounded">
                            {{ $event->registrations_count }} people already registered. If you remove a question, their answers to it are deleted.
                        </p>
                    @endif

                    <p class="mt-4 text-sm text-gray-500" x-show="blocks.length === 0">No blocks yet. Add one with the buttons below.</p>

                    <div class="mt-4 space-y-3" x-sort="reorder($item, $position)">
                        <template x-for="(block, index) in blocks" :key="block.uid">
                            <div x-sort:item="block.uid" class="border rounded bg-white">

                                <div class="flex items-center gap-2 px-3 py-2 bg-gray-50 border-b rounded-t text-xs text-gray-600">
                                    <span x-sort:handle class="cursor-grab px-1" title="Drag to reorder">
                                        <svg class="w-4 h-4" viewBox="0 0 16 16" fill="currentColor">
                                            <circle cx="5" cy="3" r="1.4"/><circle cx="11" cy="3" r="1.4"/>
                                            <circle cx="5" cy="8" r="1.4"/><circle cx="11" cy="8" r="1.4"/>
                                            <circle cx="5" cy="13" r="1.4"/><circle cx="11" cy="13" r="1.4"/>
                                        </svg>
                                    </span>
                                    <span class="font-semibold uppercase tracking-wide" x-text="typeLabel(block.type)"></span>
                                    <button type="button" x-on:click="remove(block.uid)" class="ms-auto text-red-600 underline">Remove</button>
                                </div>

                                <div class="p-3 space-y-3">
                                    <input type="hidden" :name="`blocks[${index}][id]`" :value="block.id ?? ''">
                                    <input type="hidden" :name="`blocks[${index}][type]`" :value="block.type">

                                    <input type="text" :name="`blocks[${index}][label]`" x-model="block.label"
                                           :placeholder="placeholder(block.type)"
                                           class="block w-full border-gray-300 rounded-md shadow-sm">

                                    <textarea :name="`blocks[${index}][description]`" x-model="block.description" rows="2"
                                              :placeholder="block.type === 'note' ? 'Note text' : 'Description or help text (optional)'"
                                              class="block w-full border-gray-300 rounded-md shadow-sm text-sm"></textarea>

                                    <template x-if="hasOptions(block.type)">
                                        <textarea :name="`blocks[${index}][options]`" x-model="block.options" rows="3"
                                                  placeholder="One option per line"
                                                  class="block w-full border-gray-300 rounded-md shadow-sm text-sm"></textarea>
                                    </template>

                                    <template x-if="isInput(block.type)">
                                        <div class="flex flex-wrap items-center justify-between gap-3 text-sm">
                                            <label class="flex items-center gap-2">
                                                <input type="checkbox" :name="`blocks[${index}][required]`" value="1" x-model="block.required">
                                                Required
                                            </label>
                                            <select :name="`blocks[${index}][width]`" x-model="block.width"
                                                    class="border-gray-300 rounded-md shadow-sm text-sm py-1">
                                                <option value="full">Full width</option>
                                                <option value="half">Half width</option>
                                            </select>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <template x-for="item in palette" :key="item.type">
                            <button type="button" x-on:click="add(item.type)"
                                    class="px-3 py-1 border border-gray-800 text-sm rounded-md" x-text="'+ ' + item.label"></button>
                        </template>
                    </div>
                    <p class="mt-3 text-xs text-gray-500">
                        Section heading and Note only show text. People can type in Short answer, Paragraph, Number, and Date.
                    </p>
                </div>

                

                {{-- 4. Look and feel --}}
                <div class="bg-white p-6 rounded shadow">
                    <h3 class="font-semibold text-lg">Look and feel</h3>
                    <p class="text-sm text-gray-600">Make the form fit your event.</p>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <label class="text-sm">Main color (buttons and highlights)
                            <input type="color" name="theme[primary]" x-model="theme.primary"
                                   class="block mt-1 h-10 w-full p-1 border border-gray-300 rounded-md">
                        </label>
                        <label class="text-sm">Page background
                            <input type="color" name="theme[background]" x-model="theme.background"
                                   class="block mt-1 h-10 w-full p-1 border border-gray-300 rounded-md">
                        </label>
                        <label class="text-sm">Form card color
                            <input type="color" name="theme[card]" x-model="theme.card"
                                   class="block mt-1 h-10 w-full p-1 border border-gray-300 rounded-md">
                        </label>
                        <label class="text-sm">Text color
                            <input type="color" name="theme[text]" x-model="theme.text"
                                   class="block mt-1 h-10 w-full p-1 border border-gray-300 rounded-md">
                        </label>

                        <label class="text-sm">Font
                            <select name="theme[font]" x-model="theme.font" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                <option value="sans">Clean (sans-serif)</option>
                                <option value="serif">Classic (serif)</option>
                                <option value="mono">Typewriter (mono)</option>
                                <option value="rounded">Friendly (rounded)</option>
                            </select>
                        </label>
                        <label class="text-sm">Corners
                            <select name="theme[radius]" x-model="theme.radius" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                <option value="none">Square</option>
                                <option value="md">Rounded</option>
                                <option value="xl">Very rounded</option>
                            </select>
                        </label>

                        <label class="text-sm">Form title (optional)
                            <input type="text" name="theme[title]" x-model="theme.title" placeholder="Register for {{ $event->title }}"
                                   class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                        </label>
                        <label class="text-sm">Button text
                            <input type="text" name="theme[button]" x-model="theme.button"
                                   class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                        </label>

                        <label class="text-sm sm:col-span-2">Intro text (optional)
                            <textarea name="theme[intro]" x-model="theme.intro" rows="3"
                                      class="block mt-1 w-full border-gray-300 rounded-md shadow-sm"></textarea>
                        </label>

                        <label class="flex items-center gap-2 text-sm sm:col-span-2">
                            <input type="checkbox" name="theme[show_banner]" value="1" x-model="theme.show_banner">
                            Show the event thumbnail as a banner on top of the form
                        </label>
                    </div>
                </div>

                {{-- 5. Live preview of the whole form --}}
                <div class="bg-white p-6 rounded shadow">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="font-semibold text-lg">Live preview</h3>
                            <p class="text-sm text-gray-600">Your whole form, as participants will see it. It updates as you type.</p>
                        </div>
                        <button type="button" x-on:click="openPreview($el.closest('form'))"
                                class="px-3 py-1 border border-gray-800 text-sm rounded-md">Open preview in new page</button>
                    </div>

                    <div class="mt-4 p-5 border border-gray-200"
                         :style="`background:${theme.background}; border-radius:${radii[theme.radius]}; font-family:${fonts[theme.font]}; color:${theme.text}`">
                        <div class="p-5" :style="`background:${theme.card}; border-radius:${radii[theme.radius]}`">

                            <h4 class="text-xl font-bold" x-text="theme.title || @js('Register for '.$event->title)"></h4>
                            <p class="mt-1 text-sm opacity-80 whitespace-pre-line" x-show="theme.intro" x-text="theme.intro"></p>
                            <p class="mt-3 text-xs opacity-70">Registering as <strong>Participant name</strong> (participant@email.com)</p>

                            <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-4">

                                {{-- Categories --}}
                                <template x-if="categories.length">
                                    <div class="sm:col-span-2">
                                        <div class="pb-2 border-b-2 font-semibold" :style="`border-color:${theme.primary}`">Choose your category</div>

                                        <div class="mt-2 space-y-2">
                                            <template x-for="(cat, i) in categories" :key="i">
                                                <div class="p-3 text-sm bg-white border"
                                                     :style="`border-radius:${radii[theme.radius]}; color:#111827; border-color:${i === 0 ? theme.primary : '#d1d5db'}; ${i === 0 ? 'box-shadow:0 0 0 2px ' + theme.primary : ''}`">
                                                    <div class="flex justify-between font-semibold">
                                                        <span x-text="cat.name || 'Category name'"></span>
                                                        <span x-text="'PHP ' + Number(cat.price || 0).toLocaleString('en-US', { minimumFractionDigits: 2 })"></span>
                                                    </div>
                                                    <div class="mt-1 text-xs text-gray-500" x-text="(cat.slots || 0) + ' slots left'"></div>
                                                    <ul class="list-disc ms-5 mt-1 text-xs text-gray-500">
                                                        <template x-for="(line, n) in optionList(cat.inclusions)" :key="n">
                                                            <li x-text="line"></li>
                                                        </template>
                                                    </ul>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                {{-- Blocks in the same order as the builder --}}
                                <template x-for="block in blocks" :key="block.uid">
                                    <div :class="(block.type === 'section' || block.type === 'note' || block.width !== 'half') ? 'sm:col-span-2' : ''">

                                        <template x-if="block.type === 'section'">
                                            <div>
                                                <div class="px-3 py-2 font-semibold"
                                                    :style="`background:${theme.primary}; color:${textOn(theme.primary)}; border-radius:${radii[theme.radius]}`"
                                                    x-text="block.label || 'Section heading'"></div>
                                                <p class="mt-1 text-sm opacity-70 whitespace-pre-line" x-show="block.description" x-text="block.description"></p>
                                            </div>
                                        </template>

                                        <template x-if="block.type === 'note'">
                                            <div class="p-3 text-sm whitespace-pre-line"
                                                 :style="`border-left:4px solid ${theme.primary}; background:rgba(0,0,0,.04); border-radius:${radii[theme.radius]}`">
                                                <strong x-text="block.label || 'Note title'"></strong>
                                                <div class="mt-1" x-show="block.description" x-text="block.description"></div>
                                            </div>
                                        </template>

                                        <template x-if="isInput(block.type)">
                                            <div>
                                                <label class="block text-sm font-medium">
                                                    <span x-text="block.label || 'Question'"></span>
                                                    <span x-show="block.required" class="text-red-600">*</span>
                                                </label>
                                                <p class="text-xs opacity-70 whitespace-pre-line" x-show="block.description" x-text="block.description"></p>

                                                <template x-if="['text', 'number', 'date'].includes(block.type)">
                                                    <div class="mt-1 px-3 py-2 text-sm bg-white border border-gray-300 text-gray-400"
                                                         :style="`border-radius:${radii[theme.radius]}`"
                                                         x-text="block.type === 'date' ? 'mm/dd/yyyy' : (block.type === 'number' ? '0' : 'Short answer')"></div>
                                                </template>

                                                <template x-if="block.type === 'textarea'">
                                                    <div class="mt-1 px-3 py-2 h-20 text-sm bg-white border border-gray-300 text-gray-400"
                                                         :style="`border-radius:${radii[theme.radius]}`">Long answer</div>
                                                </template>

                                                <template x-if="block.type === 'dropdown'">
                                                    <div class="mt-1 px-3 py-2 text-sm bg-white border border-gray-300 text-gray-400 flex justify-between"
                                                         :style="`border-radius:${radii[theme.radius]}`">
                                                        <span x-text="optionList(block.options)[0] || '-- Select option --'"></span>
                                                        <span>&#9662;</span>
                                                    </div>
                                                </template>

                                                <template x-if="block.type === 'radio'">
                                                    <div class="mt-1 space-y-1 text-sm">
                                                        <template x-for="(o, n) in optionList(block.options)" :key="n">
                                                            <div class="flex items-center gap-2">
                                                                <span class="inline-block w-3.5 h-3.5 rounded-full border-2" :style="`border-color:${theme.primary}`"></span>
                                                                <span x-text="o"></span>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>

                                                <template x-if="block.type === 'checkbox'">
                                                    <div class="mt-1 flex flex-wrap gap-2">
                                                        <template x-for="(o, n) in optionList(block.options)" :key="n">
                                                            <span class="inline-block px-3 py-1 text-sm border"
                                                                  :style="`border-color:${theme.primary}; border-radius:${radii[theme.radius]}`"
                                                                  x-text="o"></span>
                                                        </template>
                                                    </div>
                                                </template>

                                                <p class="mt-1 text-xs opacity-60"
                                                   x-show="hasOptions(block.type) && optionList(block.options).length === 0">Add options in the builder above.</p>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>

                            <button type="button" class="mt-6 px-6 py-3 font-semibold"
                                    :style="`background:${theme.primary}; color:${textOn(theme.primary)}; border-radius:${radii[theme.radius]}`"
                                    x-text="theme.button || 'Register'"></button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <x-primary-button>Save Form</x-primary-button>
                <button type="button" x-on:click="openPreview($el.closest('form'))"
                        class="px-4 py-2 border border-gray-800 text-sm rounded-md">Open preview in new page</button>
                <span class="text-xs text-gray-500">The preview shows what is on screen now, even before you save.</span>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('formDesigner', () => ({
                mode: @json(old('registration_mode', $event->registration_mode)),
                categories: @json($initialCategories),
                blocks: @json($initialBlocks),
                theme: @json($initialTheme),
                fonts: @json(\App\Models\Event::FONTS),
                radii: @json(\App\Models\Event::RADII),
                previewUrl: @json(route('organizer.events.preview.draft', $event)),
                counter: 0,
                palette: [
                    { type: 'section', label: 'Section heading' },
                    { type: 'note', label: 'Note' },
                    { type: 'text', label: 'Short answer' },
                    { type: 'textarea', label: 'Paragraph' },
                    { type: 'number', label: 'Number' },
                    { type: 'date', label: 'Date' },
                    { type: 'dropdown', label: 'Dropdown' },
                    { type: 'radio', label: 'Multiple choice' },
                    { type: 'checkbox', label: 'Checkboxes (buttons)' },
                ],

                init() {
                    this.blocks = this.blocks.map(block => ({
                        ...block,
                        uid: this.newUid(),
                        description: block.description ?? '',
                        options: block.options ?? '',
                        required: !!block.required,
                        width: block.width || 'full',
                    }));
                    this.theme.show_banner = !!this.theme.show_banner;
                },

                newUid() {
                    this.counter++;
                    return 'b' + this.counter;
                },

                add(type) {
                    this.blocks.push({
                        uid: this.newUid(), id: null, type: type, label: '', description: '',
                        options: '', required: false, width: 'full',
                    });
                },

                remove(uid) {
                    this.blocks = this.blocks.filter(block => block.uid !== uid);
                },

                reorder(uid, position) {
                    const from = this.blocks.findIndex(block => block.uid === uid);
                    if (from === -1) return;
                    const [item] = this.blocks.splice(from, 1);
                    this.blocks.splice(position, 0, item);
                },

                addCategory() {
                    this.categories.push({ id: null, name: '', price: '', slots: '', inclusions: '', ends_at: '', locked: false });
                },

                removeCategory(index) {
                    this.categories.splice(index, 1);
                },

                isInput(type) {
                    return !['section', 'note'].includes(type);
                },

                hasOptions(type) {
                    return ['dropdown', 'radio', 'checkbox'].includes(type);
                },

                optionList(text) {
                    return (text || '').split(/\r?\n/).map(line => line.trim()).filter(Boolean);
                },

                typeLabel(type) {
                    const found = this.palette.find(item => item.type === type);
                    return found ? found.label : type;
                },

                placeholder(type) {
                    if (type === 'section') return 'Section title (e.g. Personal Information)';
                    if (type === 'note') return 'Note title (e.g. Important note)';
                    return 'Question';
                },

                textOn(hex) {
                    const n = parseInt((hex || '#000000').slice(1), 16);
                    const r = (n >> 16) & 255, g = (n >> 8) & 255, b = n & 255;
                    return ((r * 299 + g * 587 + b * 114) / 1000) > 150 ? '#111827' : '#ffffff';
                },

                // Sends the form as it is now to the preview page in a new tab. Nothing is saved.
                openPreview(form) {
                    const oldAction = form.action;
                    const oldTarget = form.target;
                    form.action = this.previewUrl;
                    form.target = '_blank';
                    form.submit();
                    form.action = oldAction;
                    form.target = oldTarget;
                },
            }));
        });
    </script>
</x-app-layout>