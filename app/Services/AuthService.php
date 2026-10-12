<?php

namespace App\Services;

use App\Models\User;
use App\Models\Token;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Exception;

class AuthService
{
    public function register(array $data): string
    {
        if (User::where('email', $data['email'])->exists()) {
            throw new Exception("Email address is already registered.", 409);
        }

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        return $this->generateAndStoreToken($user->id);
    }

    public function login(array $credentials): string
    {
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw new Exception("Invalid email or password credentials.", 401);
        }

        return $this->generateAndStoreToken($user->id);
    }

    public function logout(string $token): void
    {
        Token::where('api_token', $token)->delete();
    }

    private function generateAndStoreToken(string $userId): string
    {
        $token = bin2hex(random_bytes(32));

        Token::create([
            'user_id'    => $userId,
            'api_token'  => $token,
            'expires_at' => Carbon::now()->addHours(2), // Exact 2 Hours Expiry
        ]);

        return $token;
    }
}