<?php

use BangNokia\Aghanim\Http\Controllers\AghanimActionController;
use Illuminate\Support\Facades\Route;

$middleware = config('aghanim.middleware', ['web', 'auth']);
$csrfProtection = config('aghanim.csrf_protection', true);

$route = Route::post('/aghanim/action', [AghanimActionController::class, 'handle'])
    ->name('aghanim.action')
    ->middleware($middleware);

if (!$csrfProtection) {
    $route->withoutMiddleware(['\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken']);
}