<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePrivateAccount
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->is_owner, 403);
        if ($request->user()->must_change_password && ! $request->routeIs('password.*', 'logout')) {
            return redirect()->route('password.edit');
        }

        return $next($request);
    }
}
