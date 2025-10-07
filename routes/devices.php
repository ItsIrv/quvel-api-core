<?php

use Illuminate\Support\Facades\Route;
use Quvel\Core\Http\Controllers\DeviceController;

Route::post('/register', [DeviceController::class, 'register'])
    ->name('register');

Route::post('/push-token', [DeviceController::class, 'updatePushToken'])
    ->name('push-token');

Route::post('/deactivate', [DeviceController::class, 'deactivate'])
    ->name('deactivate');

Route::get('/list', [DeviceController::class, 'getUserDevices'])
    ->name('list');