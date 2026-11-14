<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    public function index()
    {
        return view('admin.requests.index', [
            'pendingAccounts' => User::where('role', 'organizer')->where('account_status', 'pending')->oldest()->get(),
            'pendingEvents' => Event::with('organizer')->where('approval_status', 'pending')->oldest()->get(),
            'declinedAccounts' => User::where('role', 'organizer')->where('account_status', 'rejected')->latest('reviewed_at')->limit(10)->get(),
            'declinedEvents' => Event::with('organizer')->where('approval_status', 'rejected')->latest('reviewed_at')->limit(10)->get(),
        ]);
    }

    public function approveAccount(Request $request, User $user)
    {
        abort_unless($user->role === 'organizer', 404);

        $user->forceFill([
            'account_status' => 'approved',
            'status_note' => null,
            'reviewed_at' => now(),
            'reviewed_by' => $request->user()->id,
        ])->save();

        return back()->with('status', "{$user->name} is approved and can now create events.");
    }

    public function declineAccount(Request $request, User $user)
    {
        abort_unless($user->role === 'organizer', 404);

        $data = $request->validate(['reason' => ['nullable', 'string', 'max:300']]);

        $user->forceFill([
            'account_status' => 'rejected',
            'status_note' => $data['reason'] ?? null,
            'reviewed_at' => now(),
            'reviewed_by' => $request->user()->id,
        ])->save();

        return back()->with('status', "The organizer request of {$user->name} was declined.");
    }

    public function approveEvent(Request $request, Event $event)
    {
        $event->forceFill([
            'approval_status' => 'approved',
            'approval_note' => null,
            'reviewed_at' => now(),
            'reviewed_by' => $request->user()->id,
        ])->save();

        return back()->with('status', "Event \"{$event->title}\" is approved and public now.");
    }

    public function declineEvent(Request $request, Event $event)
    {
        $data = $request->validate(
            ['reason' => ['required', 'string', 'min:5', 'max:300']],
            ['reason.required' => 'Write a reason, so the organizer knows what to fix.']
        );

        $event->forceFill([
            'approval_status' => 'rejected',
            'approval_note' => $data['reason'],
            'reviewed_at' => now(),
            'reviewed_by' => $request->user()->id,
        ])->save();

        return back()->with('status', "Event \"{$event->title}\" was declined.");
    }
}