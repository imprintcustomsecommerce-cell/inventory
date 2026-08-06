<?php

/**
 * Vercel serverless entry point.
 *
 * Vercel requires the entry point to live in /api, so this file just forwards
 * to Laravel's normal public/index.php. Everything before that require() is
 * there because Vercel's filesystem is read-only except for /tmp, and Laravel
 * expects several writable directories.
 */

// Laravel writes compiled views, caches and sessions at runtime. On Vercel the
// project directory is read-only, so point every writable path at /tmp.
foreach ([
    '/tmp/views',
    '/tmp/framework/cache/data',
    '/tmp/framework/sessions',
    '/tmp/framework/views',
    '/tmp/logs',
] as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// Aiven refuses unencrypted MySQL connections, so PDO needs the CA certificate.
// The path has to be absolute and is only known at runtime, so it is resolved
// here rather than hard-coded into an environment variable.
$caCertificate = __DIR__ . '/../storage/certs/aiven-ca.pem';
if (is_file($caCertificate)) {
    putenv('MYSQL_ATTR_SSL_CA=' . $caCertificate);
    $_ENV['MYSQL_ATTR_SSL_CA'] = $caCertificate;
    $_SERVER['MYSQL_ATTR_SSL_CA'] = $caCertificate;
}

require __DIR__ . '/../public/index.php';
