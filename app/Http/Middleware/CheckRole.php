<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!$request->user()) {
            return redirect('/admin/login');
        }

        if (!$request->user()->hasAnyRole($roles)) {
            abort(403, 'Nemate pristup ovoj stranici.');
        }

        return $next($request);
    }
}
