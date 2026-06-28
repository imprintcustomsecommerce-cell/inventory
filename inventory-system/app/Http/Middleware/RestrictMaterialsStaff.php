<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictMaterialsStaff
{
    /**
     * Materials-department staff may only use the Materials area (plus their
     * profile and logout). Everything else redirects them back to Materials.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isMaterialsStaff()) {
            $name = $request->route()?->getName() ?? '';
            $allowed = str_starts_with($name, 'materials.')
                || str_starts_with($name, 'profile.')
                || $name === 'logout';

            if (!$allowed) {
                return redirect()->route('materials.index');
            }
        }

        return $next($request);
    }
}
