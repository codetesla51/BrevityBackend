<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\userController;
Route::get("auth/google", [UserController::class, "redirectToGoogle"]);

Route::get("auth/google/callback", [
  UserController::class,
  "handleGoogleCallback",
]);
