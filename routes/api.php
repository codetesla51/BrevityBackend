<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\userController;
use App\Http\Controllers\pdfController;

Route::post("/register", [UserController::class, "store"]);
Route::post("/login", [UserController::class, "login"]);
Route::get("/hello", [UserController::class, "hello"]);
Route::get("/error", function () {
  return response()->json(
    [
      "status" => "error",
      "message" => "Internal server error",
    ],
    500
  );
});

Route::middleware("auth:sanctum")->group(function () {
  Route::post("/pdfConvert", [PdfController::class, "ConvertPDF"]);
  Route::get("/fetchPDF", [PdfController::class, "getUserPDFs"]);
  Route::get("/downloadPDF/{id}", [PdfController::class, "downloadPDF"]);
  Route::delete("/deletePDF/{id}", [PdfController::class, "deletePDF"]);
  Route::get("/user", [UserController::class, "getUserInfo"]);
});
Route::get("/auth/google", [UserController::class, "redirectToGoogle"]);
Route::post("/exchangeToken", [UserController::class, "exchangeToken"]);
