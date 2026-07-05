<?php

use Illuminate\Contracts\View\View;

if (!function_exists('role_view')) {
    /**
     * Resolve a module view for the logged-in user's role.
     *
     * Each role has its own copy of the screens in its sidebar
     * (resources/views/{admin|store|warehouse|materials|events}/...), so a
     * page can be fixed for one role without touching the others.
     *
     * Resolution order:
     *   1. the role's own copy        e.g. admin.products.index
     *   2. the module's canonical file e.g. warehouse.products.index
     */
    function role_view(string $view, array $data = []): View
    {
        // Role => view folder that role owns.
        static $roleFolders = [
            'admin' => 'admin',
            'store' => 'store',
            'inventory' => 'warehouse',
            'materials' => 'materials',
            'events' => 'events',
        ];

        // Module (first view segment) => canonical folder.
        static $canonical = [
            'dashboard' => 'shared', 'activities' => 'shared', 'auth' => 'shared',
            'calendar' => 'shared', 'deliveries' => 'shared', 'profile' => 'shared',
            'projects' => 'shared', 'search' => 'shared',
            'staff' => 'admin', 'expenses' => 'admin', 'channels' => 'admin',
            'online-orders' => 'admin', 'promo-codes' => 'admin',
            'sales' => 'store', 'invoices' => 'store', 'quotes' => 'store',
            'portal' => 'store', 'customers' => 'store',
            'inventory' => 'warehouse', 'products' => 'warehouse', 'requests' => 'warehouse',
            'quality' => 'warehouse', 'suppliers' => 'warehouse', 'purchases' => 'warehouse',
        ];

        // Accept canonical-prefixed names too ("warehouse.products.index").
        foreach (['shared.', 'admin.', 'store.', 'warehouse.'] as $prefix) {
            if (str_starts_with($view, $prefix)) {
                $view = substr($view, strlen($prefix));
                break;
            }
        }

        $folder = $roleFolders[auth()->user()?->role] ?? null;
        if ($folder && view()->exists("{$folder}.{$view}")) {
            return view("{$folder}.{$view}", $data);
        }

        $module = explode('.', $view, 2)[0];
        $prefix = $canonical[$module] ?? null;
        if ($prefix && $prefix !== $module && view()->exists("{$prefix}.{$view}")) {
            return view("{$prefix}.{$view}", $data);
        }

        return view($view, $data);
    }
}
