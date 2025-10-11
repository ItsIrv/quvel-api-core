<?php

use Illuminate\Support\Facades\Route;
use Quvel\Core\Platform\Settings\SettingsController;

Route::get('/', [SettingsController::class, 'index'])
    ->name('index');
