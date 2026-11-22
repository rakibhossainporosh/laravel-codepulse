<?php

namespace CodePulse\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class Authorize
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('codepulse.enabled', true)) {
            abort(404);
        }

        if (app()->environment('local', 'testing')) {
            return $next($request);
        }

        if (Gate::has('viewCodePulse') && Gate::check('viewCodePulse', [$request->user()])) {
            return $next($request);
        }

        abort(403, 'Unauthorized access to CodePulse dashboard.');
    }
}
