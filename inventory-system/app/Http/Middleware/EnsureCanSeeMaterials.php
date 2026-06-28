<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanSeeMaterials
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->canSeeMaterials()) {
            abort(403, 'Materials department access required.');
        }

        return $next($request);
    }
}
