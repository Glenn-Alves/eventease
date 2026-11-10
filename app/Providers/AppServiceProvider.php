<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Only the owner admin (the first admin) can create or remove other admins.
        // To let every admin do it, change this to: $user->isAdmin()
        Gate::define('manage-admins', fn (User $user) => $user->isOwner());
    }
}