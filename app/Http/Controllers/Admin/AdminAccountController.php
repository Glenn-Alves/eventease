<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminAccountController extends Controller
{
    public function index()
    {
        $admins = User::where('role', 'admin')->orderBy('id')->get();
        $ownerId = $admins->first()?->id;

        return view('admin.admins.index', compact('admins', 'ownerId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $admin = new User([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        $admin->role = 'admin'; // set here, never from the form
        $admin->save();

        return back()->with('status', "Admin account created for {$admin->name}.");
    }

    // Removes admin access. The account becomes a normal participant account.
    public function destroy(Request $request, User $user)
    {
        abort_unless($user->isAdmin(), 404);
        abort_if($user->id === $request->user()->id, 403, 'You cannot remove your own admin access.');
        abort_if($user->isOwner(), 403, 'The owner cannot be removed.');

        $user->role = 'participant';
        $user->save();

        return back()->with('status', "{$user->name} is not an admin anymore. The account is now a participant account.");
    }
}