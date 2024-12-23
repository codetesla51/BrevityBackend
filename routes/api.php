<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\userController;
use App\Http\Controllers\pdfController;

Route::post("/register", [UserController::class, "store"]);
Route::post("/login", [UserController::class, "login"]);
Route::post("/pdfConvert", [pdfController::class, "ConvertPDF"]);
