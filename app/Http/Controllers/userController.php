<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
class UserController extends Controller
{
  public function store(Request $request)
  {
    if (!$request->expectsJson()) {
      return response()->json(
        [
          "message" =>
            "Invalid request type. This endpoint only accepts JSON requests.",
        ],
        400
      );
    }

    $validated = $request->validate([
      "name" => "required|max:255",
      "password" => "required|min:8|max:255",
      "email" => "required|email|unique:users,email",
    ]);

    try {
      $user = new User();
      $user->name = $validated["name"];
      $user->email = $validated["email"];
      $user->password = Hash::make($validated["password"]);
      $user->save();

      return response()->json(
        [
          "message" => "User registered successfully",
          "user" => $user,
        ],
        201
      );
    } catch (\Exception $e) {
      return response()->json(
        [
          "message" => "Internal Server Error",
          "error" => $e->getMessage(),
        ],
        500
      );
    }
  }
  

  public function login(Request $request)
  {
    $validated = $request->validate([
      "email" => "required|email",
      "password" => "required|min:8",
    ]);

    try {
      $user = User::where("email", $validated["email"])->first();

      if ($user && Hash::check($validated["password"], $user->password)) {
        $token = $user->createToken("auth_token")->plainTextToken;

        return response()->json(
          [
            "message" => "Login successful",
            "user" => $user,
            "token" => $token,
          ],
          200
        );
      }

      return response()->json(
        [
          "message" => "Invalid credentials",
        ],
        401
      );
    } catch (\Exception $e) {
      return response()->json(
        [
          "message" => "Internal Server Error",
          "error" => $e->getMessage(),
        ],
        500
      );
    }
  }
}
