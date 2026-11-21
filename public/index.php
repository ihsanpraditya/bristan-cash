<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Deteksi direktori core Laravel (fleksibel: core terpisah 'laravel_core' / 'core', atau satu folder)
$coreCandidates = [
    __DIR__.'/../laravel_core',
    __DIR__.'/../core',
    __DIR__.'/..',
];

$corePath = __DIR__.'/..';
foreach ($coreCandidates as $candidate) {
    if (file_exists($candidate.'/vendor/autoload.php')) {
        $corePath = $candidate;
        break;
    }
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $corePath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $corePath.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $corePath.'/bootstrap/app.php';

// Set public path ke direktori saat ini (penting saat folder public dipisah di shared hosting)
$app->usePublicPath(__DIR__);

$app->handleRequest(Request::capture());
