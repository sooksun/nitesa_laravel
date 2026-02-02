<?php

/**
 * Simple script to verify database connection.
 * Run: php check-db.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Checking database connection...\n";

try {
    $pdo = DB::connection()->getPdo();
    $name = DB::connection()->getDatabaseName();
    $driver = DB::connection()->getDriverName();

    echo "OK: Connected to database '{$name}' (driver: {$driver})\n";

    // Simple query test
    DB::select('SELECT 1 as test');
    echo "OK: Query test passed\n";

    exit(0);
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
