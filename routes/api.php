<?php

use App\Http\Controllers\Api\AlertController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DoseEventController;
use App\Http\Controllers\Api\MedicationScheduleController;
use App\Http\Controllers\Api\PatientController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get('/dashboard/summary', [DashboardController::class, 'summary']);

    Route::get('/patients', [PatientController::class, 'index']);
    Route::post('/patients', [PatientController::class, 'store']);
    Route::get('/patients/{patient}', [PatientController::class, 'show']);
    Route::patch('/patients/{patient}', [PatientController::class, 'update']);
    Route::delete('/patients/{patient}', [PatientController::class, 'destroy']);

    Route::get('/patients/{patient}/medication-schedules', [MedicationScheduleController::class, 'indexByPatient']);
    Route::post('/patients/{patient}/medication-schedules', [MedicationScheduleController::class, 'store']);
    Route::patch('/medication-schedules/{medicationSchedule}', [MedicationScheduleController::class, 'update']);
    Route::delete('/medication-schedules/{medicationSchedule}', [MedicationScheduleController::class, 'destroy']);

    Route::post('/dose-events', [DoseEventController::class, 'store']);
    Route::get('/medication-history', [DoseEventController::class, 'history']);

    Route::get('/alerts', [AlertController::class, 'index']);
    Route::patch('/alerts/{alert}/acknowledge', [AlertController::class, 'acknowledge']);
});
