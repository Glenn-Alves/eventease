<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\EventFiles;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search'));
        $role = in_array($request->query('role'), ['organizer', 'participant', 'admin'], true)
            ? $request->query('role')
            : null;

        $users = User::withCount(['events', 'registrations'])
            ->when($role, fn ($query) => $query->where('role', $role))
            ->when($search !== '', fn ($query) => $query->where(fn ($inner) => $inner
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'search', 'role'));
    }

    public function destroy(Request $request, User $user)
    {
        abort_if($user->id === $request->user()->id, 403, 'You cannot delete your own account here.');
        abort_if($user->isAdmin(), 403, 'Admin accounts are managed in the Admins section.');

        // Delete the posters and gallery images of their events, then the account
        foreach ($user->events()->with('galleryItems')->get() as $event) {
            EventFiles::delete($event);
        }

        $name = $user->name;
        $user->delete(); // their events and registrations go with it

        return back()->with('status', "The account of {$name} was deleted.");
    }
}