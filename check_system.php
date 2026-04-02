<?php

use App\Models\Organization;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;

if (PHP_VERSION_ID >= 80500) {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
}

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

try {
    echo "Checking database...\n";

    $organizationCount = Organization::count();
    $userCount = User::count();

    echo "Organizations: {$organizationCount}\n";
    echo "Users: {$userCount}\n";

    if ($organizationCount === 0) {
        echo "Warning: no organizations found.\n";
    }

    echo "\nChecking logs...\n";

    $logFile = storage_path('logs/laravel.log');

    if (! file_exists($logFile)) {
        echo "No application log file found yet.\n";
        exit(0);
    }

    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    $since = (new DateTimeImmutable('now'))->modify('-30 minutes');
    $errorLines = [];

    foreach ($lines as $line) {
        if (! (str_contains($line, 'ERROR') || str_contains($line, '.ERROR:'))) {
            continue;
        }

        if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $line, $matches) === 1) {
            $timestamp = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $matches[1]);

            if ($timestamp instanceof DateTimeImmutable && $timestamp < $since) {
                continue;
            }
        }

        $errorLines[] = $line;
    }

    if ($errorLines === []) {
        echo "No recent error entries found.\n";
        exit(0);
    }

    echo "Latest error (last 30m):\n";
    echo end($errorLines)."\n";
} catch (Throwable $exception) {
    fwrite(STDERR, "System check failed: {$exception->getMessage()}\n");
    exit(1);
}
