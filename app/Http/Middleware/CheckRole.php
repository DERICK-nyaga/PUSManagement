<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = Auth::user();
        if (!Auth::check() || Auth::user()->role !== $role) {
            abort(403, "Unauthorized action. {$role} access required.");
        }

        return $next($request);
    }
}
