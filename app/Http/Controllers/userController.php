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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
class UserController extends Controller
{
  /**
   * Register a new user
   */
  public function store(Request $request): JsonResponse
  {
    $ipAddress = $request->ip();
    $existingUser = User::where("ip_address", $ipAddress)->first();

    if ($existingUser) {
      return response()->json(
        [
          "message" => "You are already registered",
          "action" => "login",
          "email" => $existingUser->email,
        ],
        422
      );
    }

    // Validate the request
    $validatedData = $request->validate([
      "name" => ["required", "string", "max:255"],
      "password" => ["required", "string", "min:8"],
      "email" => ["required", "string", "email", "max:255", "unique:users"],
    ]);

    try {
      // Create the user
      $user = User::create([
        "name" => $validatedData["name"],
        "email" => $validatedData["email"],
        "password" => Hash::make($validatedData["password"]),
        "ip_address" => $ipAddress,
      ]);

      // Generate the token for the user
      $token = $user->createToken("auth_token")->plainTextToken;

      return response()->json(
        [
          "message" => "User registered successfully",
          "token" => $token,
        ],
        201
      );
    } catch (\Exception $e) {
      report($e); // Log the error for debugging
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
    return response()->json(
      Auth::user()->only(["name", "email", "used_credits","max_credits"])
    );
  }

  /**
   * Redirect to Google OAuth
   */
  public function redirectToGoogle()
  {
    $url = Socialite::driver("google")
      ->stateless()
      ->redirect()
      ->getTargetUrl();
    return response()->json(["url" => $url]);
  }

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

      $tempCode = Str::random(32);
      Cache::put(
        "oauth_temp_code:{$tempCode}",
        [
          "user_id" => $user->id,
          "email" => $user->email,
        ],
        now()->addMinutes(5)
      );
      $clientUrl = env("CLIENT_URL") . "/auth";

      return redirect()->away("{$clientUrl}?code={$tempCode}");
    } catch (\Exception $e) {
      $clientUrl = env("CLIENT_URL") . "/auth";

      return redirect()->away("{$clientUrl}?error=Authentication failed");
    }
  }
  public function exchangeToken(Request $request)
  {
    try {
      $request->validate([
        "code" => "required|string|max:32",
      ]);

      $cacheKey = "oauth_temp_code:" . $request->code;
      $tempData = Cache::get($cacheKey);

      if (!$tempData) {
        throw new \Exception("Invalid or expired code");
      }

      Cache::forget($cacheKey);

      $user = User::findOrFail($tempData["user_id"]);
      $token = $user->createToken("GoogleLoginToken")->plainTextToken;

      return response()->json(["token" => $token]);
    } catch (\Exception $e) {
      return response()->json(
        [
          "error" => $e->getMessage(),
        ],
        400
      );
    }
  }
}
