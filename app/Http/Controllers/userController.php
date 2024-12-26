<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Validation\Rules\Password;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class UserController extends Controller
{
  /**
   * Register a new user
   */
  public function store(Request $request): JsonResponse
  {
    $validated = $request->validate([
      "name" => ["required", "string", "max:255"],
      "password" => [
        "required",
        Password::min(8)
          ->mixedCase()
          ->numbers()
          ->symbols()
          ->uncompromised(),
      ],
      "email" => ["required", "string", "email", "max:255", "unique:users"],
    ]);

    try {
      $user = User::create([
        "name" => $validated["name"],
        "email" => $validated["email"],
        "password" => Hash::make($validated["password"]),
      ]);

      return response()->json(
        [
          "message" => "User registered successfully",
          "user" => $user->only(["name", "email"]),
        ],
        201
      );
    } catch (\Exception $e) {
      report($e); // Log the error
      return response()->json(
        [
          "message" => "Failed to register user",
        ],
        500
      );
    }
  }

  /**
   * Authenticate user and create token
   */
  public function login(Request $request): JsonResponse
  {
    $validated = $request->validate([
      "email" => ["required", "string", "email"],
      "password" => ["required", "string", "min:8"],
    ]);

    try {
      if (!Auth::attempt($validated)) {
        return response()->json(
          [
            "message" => "Invalid credentials",
          ],
          401
        );
      }

      $user = Auth::user();
      $token = $user->createToken("auth_token")->plainTextToken;

      return response()->json([
        "message" => "Login successful",
        "user" => $user->only(["name", "email"]),
        "token" => $token,
      ]);
    } catch (\Exception $e) {
      report($e);
      return response()->json(
        [
          "message" => "Authentication failed",
        ],
        500
      );
    }
  }

  /**
   * Get authenticated user information
   */
  public function getUserInfo(Request $request): JsonResponse
  {
    return response()->json(Auth::user()->only(["name", "email"]));
  }

  /**
   * Redirect to Google OAuth
   */
  public function redirectToGoogle()
  {
    try {
      // Redirect the user directly to the Google OAuth login page
      return Socialite::driver("google")
        ->stateless()
        ->redirect();
    } catch (\Exception $e) {
      report($e);
      return response()->json(
        [
          "message" => "Failed to initialize Google login",
        ],
        500
      );
    }
  }

  /**
   * Handle Google OAuth callback
   */
  public function handleGoogleCallback()
  {
    try {
      $googleUser = Socialite::driver("google")
        ->stateless()
        ->user();
      $user = User::updateOrCreate(
        ["email" => $googleUser->getEmail()],
        [
          "name" => $googleUser->getName(),
          "password" => Hash::make(Str::random(24)),
          "password_setup" => false,
        ]
      );

      $token = $user->createToken("GoogleLoginToken")->plainTextToken;

      return response()->json([
        "message" => $user->wasRecentlyCreated
          ? "Account created successfully"
          : "Login successful",
        "user" => $user->only(["name", "email"]),
        "token" => $token,
      ]);
    } catch (\Exception $e) {
      \Log::error("Google auth error:", [
        "message" => $e->getMessage(),
        "trace" => $e->getTraceAsString(),
      ]);

      return response()->json(
        [
          "message" => "Google authentication failed",
          "error" => $e->getMessage(),
        ],
        500
      );
    }
  }
}
