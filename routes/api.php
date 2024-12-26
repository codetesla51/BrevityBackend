<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\userController;

Route::post("/register", [UserController::class, "store"]);
Route::post("/login", [UserController::class, "login"]);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/pdfConvert', [PdfController::class, 'ConvertPDF']);
    Route::get('/user', [UserController::class, 'getUserInfo']);
});