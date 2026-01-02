<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
 
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\CsStaffController;
use App\Http\Controllers\BookingCategoryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\AuthVerificationController;
use App\Http\Controllers\ExternalMovieController;
 

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/verify-email/{id}/{hash}', [AuthVerificationController::class, 'verify'])
    ->middleware('signed')
    ->name('verification.verify');
Route::post('/email/verification-notification', [AuthVerificationController::class, 'publicResend'])
    ->middleware('throttle:6,1');

Route::apiResource('rooms', RoomController::class)->only(['index', 'show']);
Route::get('/rooms/{id}/reviews', [ReviewController::class, 'roomReviews']);

Route::apiResource('buildings', BuildingController::class)->only(['index', 'show']);
Route::apiResource('facilities', FacilityController::class)->only(['index', 'show']);
Route::apiResource('booking-categories', BookingCategoryController::class)->only(['index', 'show']);
Route::apiResource('cs-staff', CsStaffController::class)->only(['index', 'show']);
 

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::put('/users/{id}', [UserController::class, 'update']); 


    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::get('/users/{id}/bookings', [BookingController::class, 'myBookings']);
    Route::get('/bookings/{id}', [BookingController::class, 'show']);
    Route::put('/bookings/{id}/status', [BookingController::class, 'updateStatus']);
    Route::post('/bookings/{id}/disposisi', [BookingController::class, 'uploadDisposisi']);
    Route::delete('/bookings/{id}', [BookingController::class, 'destroy']);


    Route::post('/reports', [ReportController::class, 'store']);
    Route::get('/reports', [ReportController::class, 'index']);
    Route::get('/users/{id}/reports', [ReportController::class, 'myReports']);
    Route::get('/reports/{id}', [ReportController::class, 'show']);
    Route::put('/reports/{id}/status', [ReportController::class, 'updateStatus']);

    Route::get('/reviews', [ReviewController::class, 'index']);
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::get('/users/{id}/reviews', [ReviewController::class, 'myReviews']);
    Route::get('/reviews/{id}', [ReviewController::class, 'show']);
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);

    Route::get('/movies/search', [ExternalMovieController::class, 'index']);
    Route::get('/movies/{id}', [ExternalMovieController::class, 'show']);

});
 
Route::middleware('apikey')->group(function () {
    Route::apiResource('rooms', RoomController::class)->except(['index', 'show']);
    Route::apiResource('buildings', BuildingController::class)->except(['index', 'show']);
    Route::apiResource('facilities', FacilityController::class)->except(['index', 'show']);
    Route::apiResource('cs-staff', CsStaffController::class)->except(['index', 'show']);
    Route::apiResource('booking-categories', BookingCategoryController::class)->except(['index', 'show']);
});