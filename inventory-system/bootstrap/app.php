<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Hosted behind a TLS-terminating proxy (Vercel), which forwards the
        // original scheme in X-Forwarded-Proto. Without trusting it Laravel
        // sees plain HTTP and generates http:// asset and route URLs, which a
        // browser on an https:// page then blocks as mixed content.
        // Requests can only reach the app through the platform's proxy, so
        // there is no untrusted hop to restrict this to. On the LAN there is
        // no proxy and these headers are never present.
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'importer' => \App\Http\Middleware\EnsureUserCanImport::class,
            'materials' => \App\Http\Middleware\EnsureCanSeeMaterials::class,
            'dept' => \App\Http\Middleware\RestrictMaterialsStaff::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
