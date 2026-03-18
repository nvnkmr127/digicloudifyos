<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Checking Database...\n";
    $orgCount = \App\Models\Organization::count();
    echo "Organization Count: " . $orgCount . "\n";
    
    $userCount = \App\Models\User::count();
    echo "User Count: " . $userCount . "\n";
    
    if ($orgCount > 0) {
        echo "Database connection and models are working.\n";
    } else {
        echo "WARNING: No organizations found. Seeding might have failed or not run.\n";
    }

    echo "\nChecking Logs...\n";
    $logFile = storage_path('logs/laravel.log');
    if (file_exists($logFile)) {
        $logs = file_get_contents($logFile);
        if (strpos($logs, 'ERROR') !== false) {
             // Get last error
             $lines = file($logFile);
             $lastError = '';
             for ($i = count($lines) - 1; $i >= 0; $i--<?php

require __DIR__ . '/vend($
reqs[$$app = require_once __DIR__ . '/bootstra  $kernel = $app->make(Illuminate\Contracts\Console\ b$kernel->bootstrap();

try {
    echo "Checking Database...\n";
Er
try {
    echo "Chetr(    tE    $orgCount = \App\Models\Organ}     echo "Organization Count: " . $orgCount . "\nel    
    $userCount = \App\Models\User::count();
  f    n    echo "User Count: " . $userCount . "\ne)    
    if ($orgCount > 0) {
        echo et   sa     . "\n";
}
