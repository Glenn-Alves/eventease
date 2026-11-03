<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckInController extends Controller
{
    public function show(Event $event)
    {
        $this->checkOwner($event);

        $event->load(['registrations' => fn ($query) => $query->with(['user', 'category'])]);

        $rows = $event->registrations
            ->sortBy(fn ($registration) => mb_strtolower($registration->user->name))
            ->map(fn ($registration) => [
                'id' => $registration->id,
                'name' => $registration->user->name,
                'category' => $registration->category->name ?? '',
                'code' => $registration->ticket_code,
                'at' => $registration->checked_in_at?->format('M d, h:i A'),
            ])
            ->values();

        return view('organizer.events.check-in', compact('event', 'rows'));
    }

    // Called by the scanner and by the manual code box
    public function store(Request $request, Event $event): JsonResponse
    {
        $this->checkOwner($event);

        $request->validate(['code' => ['required', 'string', 'max:64']]);

        $code = strtoupper(trim($request->input('code')));

        $registration = $event->registrations()
            ->with(['user', 'category'])
            ->where('ticket_code', $code)
            ->first();

        if (! $registration) {
            return response()->json([
                'status' => 'not_found',
                'title' => 'Ticket not found',
                'detail' => 'No ticket with the code '.$code.' for this event.',
            ], 404);
        }

        // Only one request can win this update, so the same ticket never counts twice
        $updated = Registration::whereKey($registration->id)
            ->whereNull('checked_in_at')
            ->update(['checked_in_at' => now(), 'checked_in_by' => auth()->id()]);

        $registration->refresh();

        $time = $registration->checked_in_at->format('M d, h:i A');

        if ($updated === 0) {
            return response()->json([
                'status' => 'already',
                'title' => 'Already checked in',
                'detail' => $registration->user->name.' was checked in at '.$time.'.',
                'ticket_code' => $registration->ticket_code,
                'checked_in_at' => $time,
            ]);
        }

        return response()->json([
            'status' => 'checked_in',
            'title' => 'Checked in',
            'detail' => $registration->user->name
                .($registration->category ? ' | '.$registration->category->name : ''),
            'ticket_code' => $registration->ticket_code,
            'checked_in_at' => $time,
        ]);
    }

    public function undo(Request $request, Event $event): JsonResponse
    {
        $this->checkOwner($event);

        $request->validate(['registration_id' => ['required', 'integer']]);

        $registration = $event->registrations()
            ->with('user')
            ->findOrFail($request->input('registration_id'));

        $registration->forceFill(['checked_in_at' => null, 'checked_in_by' => null])->save();

        return response()->json([
            'status' => 'undone',
            'title' => 'Check-in removed',
            'detail' => $registration->user->name.' is not checked in anymore.',
        ]);
    }

    private function checkOwner(Event $event): void
    {
        abort_unless($event->user_id === auth()->id(), 403);
    }
}