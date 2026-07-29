<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanImport
{
    /**
     * Allow admins and the inventory (stockroom) role to import.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !($user->isAdmin() || $user->isInventory())) {
            abort(403, 'You do not have permission to import.');
        }

        return $next($request);
    }
}
