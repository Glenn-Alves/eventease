<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <h2 class="font-semibold text-xl text-gray-800">{{ $registration->user->name }}</h2>
            <a href="{{ route('organizer.events.show', $event) }}" class="text-sm underline text-gray-600">
                Back to {{ $event->title }}
            </a>
        </div>
    </x-slot>

    <div class="py-12 max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

        {{-- Who registered --}}
        <div class="bg-white rounded shadow p-6">
            <dl class="grid gap-4 sm:grid-cols-2 text-sm">
                <div>
                    <dt class="text-gray-500">Name</dt>
                    <dd class="font-medium">{{ $registration->user->name }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Email</dt>
                    <dd class="font-medium">{{ $registration->user->email }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Category</dt>
                    <dd class="font-medium">
                        @if ($registration->category)
                            {{ $registration->category->name }}
                            (PHP {{ number_format($registration->category->price, 2) }})
                        @else
                            -
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-gray-500">Ticket code</dt>
                    <dd class="font-mono font-bold tracking-widest">{{ $registration->ticket_code }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Registered on</dt>
                    <dd class="font-medium">{{ $registration->created_at->format('F d, Y h:i A') }}</dd>
                </div>
            </dl>
        </div>

        {{-- Answers, in the same order as the form --}}
        <div class="bg-white rounded shadow p-6">
            <h3 class="font-semibold text-lg">Answers</h3>

            @php
                $hasQuestions = $event->fields->contains(fn ($field) => ! in_array($field->type, ['section', 'note']));
            @endphp

            @if (! $hasQuestions)
                <p class="mt-3 text-sm text-gray-500">This event's form has no questions.</p>
            @else
                <div class="mt-4 space-y-4">
                    @foreach ($event->fields as $field)
                        @if ($field->type === 'section')
                            <div class="pt-2 pb-1 border-b font-semibold text-gray-800">{{ $field->label }}</div>

                        @elseif ($field->type === 'note')
                            {{-- Notes only show text, they have no answer --}}

                        @else
                            @php
                                $answer = $answers[$field->id] ?? null;

                                if ($field->type === 'date' && filled($answer) && strtotime($answer)) {
                                    $answer = \Carbon\Carbon::parse($answer)->format('F d, Y');
                                }
                            @endphp

                            <div>
                                <p class="text-sm text-gray-500">{{ $field->label }}</p>

                                @if (filled($answer))
                                    <p class="font-medium whitespace-pre-line">{{ $answer }}</p>
                                @else
                                    <p class="text-sm italic text-gray-400">No answer</p>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>