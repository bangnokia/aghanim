<?php

use BangNokia\Aghanim\Http\Controllers\AghanimActionController;
use Illuminate\Support\Facades\Route;

Route::post('/aghanim/action', [AghanimActionController::class, 'handle'])->name('aghanim.action');