<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GoogleLoginController extends Controller
{
    public function handleGoogleCallback(Request $request)
    {
        try {
            // Validate the incoming request
            $request->validate([
                'access_token' => 'required|string'
            ]);

            // Get the ID token from the request
            $token = $request->access_token;

            // Get the Google user using the token
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->userFromToken($token);
            
            Log::info('Google', $googleUser);
            // Find or create the user
            $user = User::updateOrCreate(
                ['email' => $googleUser->email],
                [
                    'name' => $googleUser->name,
                    'google_id' => $googleUser->id,
                    'password' => bcrypt(Str::random(16)),
                    'avatar' => $googleUser->avatar
                ]
            );

            // Create token using Sanctum
            $token = $user->createToken('auth-token')->plainTextToken;

            // Return the user and token
            return response()->json([
                'user' => $user,
                'token' => $token
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Authentication failed',
                'error' => $e->getMessage()
            ], 401);
        }
    }
}