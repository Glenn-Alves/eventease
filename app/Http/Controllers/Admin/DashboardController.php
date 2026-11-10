<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $users = User::selectRaw('role, COUNT(*) as total')->groupBy('role')->pluck('total', 'role');

        return view('admin.dashboard', [
            'users' => $users,
            'totalUsers' => (int) $users->sum(),
            'totalEvents' => Event::count(),
            'upcomingEvents' => Event::whereDate('event_date', '>=', today())->count(),
            'totalRegistrations' => Registration::count(),
            'checkedIn' => Registration::whereNotNull('checked_in_at')->count(),
            'recentEvents' => Event::with('organizer')->withCount('registrations')->latest()->limit(5)->get(),
            'recentUsers' => User::latest()->limit(5)->get(),
        ]);
    }
}