<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // register new user
    public function register(RegisterRequest $request)
    {
        // Validate the request...
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        return response()->json([
            'data' => [
                'user' => $user,
                'token' => $user->createToken('auth_token')->plainTextToken,
            ],
            'message' => 'Successfully created user!'
        ], 201);
    }

    // login user
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'data' => null,
                'message' => 'User not found',
            ], 404);
        }

        // verify password with bcrypt
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'data' => null,
                'message' => 'Invalid password',
            ], 401);
        }

        // generate token
        $token = $user->createToken('auth_token')->plainTextToken;

        // send response
        return response()->json([
            'data' => [
                'user' => $user,
                'access_token' => $token,
            ],
            'message' => 'Successfully logged in',
        ], 200);
    }

    // get authenticated user
    public function me()
    {
        return response()->json(auth()->user());
    }

    // logout user
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    // respond with token
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
        ]);
    }
}
