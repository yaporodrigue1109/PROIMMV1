<?php
// public/clear-cache.php

// Chemin vers le bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Exécuter les commandes
\Artisan::call('route:clear');
\Artisan::call('config:clear');
\Artisan::call('cache:clear');
\Artisan::call('view:clear');

echo "Caches cleared!<br>";
echo "Routes cleared: " . \Artisan::output();