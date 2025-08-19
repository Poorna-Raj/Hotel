<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\RoomController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post("/register", [AuthController::class, "register"]);
Route::post("/login", [AuthController::class, "login"]);
Route::post("/logout", [AuthController::class, "logout"])->middleware("auth:sanctum");

Route::apiResource("rooms", RoomController::class);
Route::put("/rooms/status/{room}", [RoomController::class, "changeStatus"]);

Route::apiResource("booking", BookingController::class);
Route::get('/rooms/bookings/stats', [BookingController::class, 'roomBookingStats']);
Route::get("/bookings/stats", [BookingController::class, "getBookingStatusForMonth"]);
Route::get("/bookings/month", [BookingController::class, "monthlyTrend"]);
Route::get("/bookings/current", [BookingController::class, "currentBookings"]);
Route::get("/bookings/ongoing", [BookingController::class, "onGoingBookings"]);
Route::get("/bookings/checkOuts", [BookingController::class, "checkedOutBookings"]);
Route::put("/bookings/{booking}/checkin", [BookingController::class, "updateCheckIn"]);
Route::put("/bookings/{booking}/checkout", [BookingController::class, "updateCheckOut"]);