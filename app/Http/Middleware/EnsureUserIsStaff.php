<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Kept for the `staff` middleware alias; teachers only.
 */
class EnsureUserIsStaff
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isTeacher()) {
            abort(403);
        }

        return $next($request);
    }
}
