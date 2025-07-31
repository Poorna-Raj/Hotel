<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $feilds = $request->validate([
                "name" => "required|max:255",
                "email" => "required|email|unique:users",
                "password" => "required|confirmed"
            ]);
            $feilds['password'] = Hash::make($feilds['password']);
            $user = User::create($feilds);

            $token = $user->createToken($request->name);

            return response()->json([
                "success" => true,
                "message" => "User Registerd Successfully",
                "data" => $user,
                "token" => $token->plainTextToken
            ], 200);
        } catch (Exception $ex) {
            return response()->json([
                "success" => false,
                "message" => "User creation failed due to" . $ex->getMessage()
            ], 500);
        }

    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                "email" => "required|email|exists:users",
                "password" => "required"
            ]);

            $user = User::where("email", $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    "success" => false,
                    "message" => "Invalid User"
                ], 401);
            }

            $token = $user->createToken($user->name);

            return response()->json([
                "success" => true,
                "message" => "Login Success",
                "token" => $token->plainTextToken
            ], 200);
        } catch (Exception $ex) {
            return response()->json([
                "success" => false,
                "message" => "Login failed due to" . $ex->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();
            return response()->json([
                "success" => true,
                "message" => "Logout Success"
            ], 200);
        } catch (Exception $ex) {
            return response()->json([
                "success" => false,
                "message" => "Logout failed due to" . $ex->getMessage()
            ], 500);
        }
    }
}
