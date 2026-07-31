<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanImport
{
    /**
     * Allow admins, the inventory (stockroom) role, and store staff to import.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !($user->isAdmin() || $user->isInventory() || $user->isStore())) {
            abort(403, 'You do not have permission to import.');
        }

        return $next($request);
    }
}
