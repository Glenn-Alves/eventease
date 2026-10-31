<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
public function handle(Request $request, Closure $next, string $role): Response
{
    $user = $request->user();

    abort_unless($user && in_array($user->role, ['organizer', 'participant'], true), 403);

    // Wrong account type: send the person to their own dashboard instead of an error page
    if ($user->role !== $role) {
        return redirect()->route($user->role === 'organizer' ? 'organizer.dashboard' : 'participant.dashboard');
    }

    return $next($request);
}
}
