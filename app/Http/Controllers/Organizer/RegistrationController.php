<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;


class RegistrationController extends Controller
{
    // One participant's registration with all their answers
    public function show(Event $event, Registration $registration)
    {
        $this->checkOwner($event);
        abort_unless($registration->event_id === $event->id, 404);

        $registration->load(['user', 'category', 'answers']);
        $event->load('fields');

        $answers = $registration->answers->pluck('answer', 'event_field_id');

        return view('organizer.registrations.show', compact('event', 'registration', 'answers'));
    }

    // All registrations of the event as a CSV file
    public function export(Event $event): StreamedResponse
    {
        $this->checkOwner($event);

        $fields = $event->fields()
            ->whereNotIn('type', ['section', 'note'])
            ->get();

        $registrations = $event->registrations()
            ->with(['user', 'category', 'answers'])
            ->orderBy('created_at')
            ->get();

        $filename = (Str::slug($event->title) ?: 'event').'-registrations.csv';

        return response()->streamDownload(function () use ($fields, $registrations) {
            $out = fopen('php://output', 'w');

            // Byte order mark, so Excel reads special characters correctly
            fwrite($out, "\xEF\xBB\xBF");

            $header = array_merge(
                ['Name', 'Email', 'Category', 'Ticket code', 'Registered on'],
                $fields->map(fn ($field) => $this->safeCell($field->label))->all()
            );
            fputcsv($out, $header, ',', '"', '');

            foreach ($registrations as $registration) {
                $answers = $registration->answers->pluck('answer', 'event_field_id');

                $row = array_merge(
                    [
                        $this->safeCell($registration->user->name),
                        $this->safeCell($registration->user->email),
                        $this->safeCell($registration->category->name ?? ''),
                        $registration->ticket_code,
                        $registration->created_at->format('Y-m-d H:i'),
                    ],
                    $fields->map(fn ($field) => $this->safeCell($answers[$field->id] ?? ''))->all()
                );

                fputcsv($out, $row, ',', '"', '');
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function checkOwner(Event $event): void
    {
        abort_unless($event->user_id === auth()->id(), 403);
    }

    // A cell that starts with = + - @ can run as a formula when the file opens in Excel.
    // A leading apostrophe makes it plain text.
    private function safeCell(?string $value): string
    {
        $value = (string) $value;

        if ($value === '' || is_numeric($value)) {
            return $value;
        }

        return preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }
}