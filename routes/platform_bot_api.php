<?php

use App\Http\Controllers\Platform\BotApi\AppointmentBotApiController;
use App\Http\Controllers\Platform\BotApi\PatientBotApiController;
use App\Http\Controllers\Platform\BotApi\AvailabilityBotApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Bot API Routes
|--------------------------------------------------------------------------
|
| Rotas da API pública para integração de bots (WhatsApp, etc.)
| Todas as rotas são protegidas por token via middleware platform.bot.token
|
*/

Route::prefix('appointments')->group(function () {
    Route::post('/create', [AppointmentBotApiController::class, 'create']);
    Route::post('/reschedule', [AppointmentBotApiController::class, 'reschedule']);
    Route::post('/cancel', [AppointmentBotApiController::class, 'cancel']);
    Route::get('/by-phone/{phone}', [AppointmentBotApiController::class, 'byPhone']);
});

Route::prefix('patients')->group(function () {
    Route::post('/create', [PatientBotApiController::class, 'create']);
    Route::get('/by-phone/{phone}', [PatientBotApiController::class, 'byPhone']);
});

Route::prefix('availability')->group(function () {
    Route::get('/slots', [AvailabilityBotApiController::class, 'slots']);
});

