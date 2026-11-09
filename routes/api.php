<?php

use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CarrierController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ExceptionController;
use App\Http\Controllers\Api\ExternalController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ShipmentController;
use App\Http\Controllers\Api\TrackingController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Data live dari API luar (semua user yang login)
    Route::get('/rates', [ExternalController::class, 'rates']);
    Route::get('/weather', [ExternalController::class, 'weather']);
    Route::get('/news', [ExternalController::class, 'news']);

    // Notifikasi (milik user sendiri)
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('role:admin,operator');

    // Audit log: admin saja
    Route::get('/audit-logs', [AuditLogController::class, 'index'])
        ->middleware('role:admin');

    // Carriers: lihat = admin & operator, kelola = admin saja
    Route::apiResource('carriers', CarrierController::class)
        ->only(['index', 'show'])
        ->middleware('role:admin,operator');

    Route::apiResource('carriers', CarrierController::class)
        ->only(['store', 'update', 'destroy'])
        ->middleware('role:admin');

    // Locations: lihat = admin & operator, kelola = admin saja
    Route::apiResource('locations', LocationController::class)
        ->only(['index', 'show'])
        ->middleware('role:admin,operator');

    Route::apiResource('locations', LocationController::class)
        ->only(['store', 'update', 'destroy'])
        ->middleware('role:admin');

    // Shipments: lihat = semua role (customer hanya miliknya), kelola = admin & operator
    Route::apiResource('shipments', ShipmentController::class)
        ->only(['index', 'show']);

    Route::apiResource('shipments', ShipmentController::class)
        ->only(['store', 'update'])
        ->middleware('role:admin,operator');

    Route::apiResource('shipments', ShipmentController::class)
        ->only(['destroy'])
        ->middleware('role:admin');

    // Tracking (path baru sesuai brief)
    Route::get('shipments/{shipment}/tracking', [TrackingController::class, 'index']);

    Route::post('shipments/{shipment}/tracking', [TrackingController::class, 'store'])
        ->middleware('role:admin,operator');

    // Tracking (path lama dipertahankan supaya frontend tidak rusak)
    Route::get('shipments/{shipment}/events', [TrackingController::class, 'index']);

    Route::post('shipments/{shipment}/events', [TrackingController::class, 'store'])
        ->middleware('role:admin,operator');

    Route::post('shipments/{shipment}/status', [TrackingController::class, 'updateStatus'])
        ->middleware('role:admin,operator');

    // Exceptions
    Route::get('shipments/{shipment}/exceptions', [ExceptionController::class, 'index']);

    Route::post('shipments/{shipment}/exceptions', [ExceptionController::class, 'store'])
        ->middleware('role:admin,operator');

    // Path baru sesuai brief
    Route::patch('exceptions/{exception}', [ExceptionController::class, 'updateStatus'])
        ->middleware('role:admin,operator');

    // Path lama dipertahankan
    Route::patch('exceptions/{exception}/status', [ExceptionController::class, 'updateStatus'])
        ->middleware('role:admin,operator');
});