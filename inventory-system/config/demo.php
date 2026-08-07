<?php

return [

    /*
     * Shows the demo account picker on the login page.
     *
     * Off unless DEMO_MODE is explicitly set, so the LAN installation — where
     * these are real staff accounts — never advertises credentials. It is only
     * enabled on the public portfolio deployment.
     */
    'enabled' => (bool) env('DEMO_MODE', false),

    /*
     * The shared password for the seeded demo accounts. Kept in config rather
     * than hard-coded in the view so rotating it does not mean editing markup.
     */
    'password' => env('DEMO_PASSWORD', 'password'),

    /*
     * The seeded roles, in the order they should be offered. Emails match
     * DatabaseSeeder.
     */
    'accounts' => [
        ['label' => 'Admin', 'email' => 'admin@imprint.ph', 'blurb' => 'Full access to every area'],
        ['label' => 'Inventory', 'email' => 'warehouse@imprint.ph', 'blurb' => 'Stockroom — creates products'],
        ['label' => 'Store', 'email' => 'store@imprint.ph', 'blurb' => 'Retail front — sales'],
        ['label' => 'Materials', 'email' => 'materials@imprint.ph', 'blurb' => 'Raw materials, suppliers'],
        ['label' => 'Events', 'email' => 'events@imprint.ph', 'blurb' => 'Pop-up / event booth'],
    ],

];
