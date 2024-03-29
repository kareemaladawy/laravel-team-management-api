<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
// use App\Traits\HttpResponses;
// use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use App\Http\Traits\HttpResponses;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UserResource;

class AuthController extends Controller
{
    use HttpResponses;

    public function login(LoginUserRequest $request)
    {
        $request->validated();

        if (!Auth::attempt($request->only(['email', 'password']))) {
            return response()->json([
                'message' => 'Credentials do not match'
            ], 401);
        }

        $user = User::where('email', $request->email)
            ->first();

        $user->tokens()->delete();

        return response()->json([
            'user' => new UserResource($user),
            'token' => $user->createToken('Token of ' . $user->name)->plainTextToken,
        ], 200);
    }

    public function register(StoreUserRequest $request)
    {
        $request->validated();

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'user created.',
            'user' => new UserResource($user),
            'token' => $user->createToken('Token of ' . $user->name)->plainTextToken
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'logged out.'
        ], 200);
    }
}
