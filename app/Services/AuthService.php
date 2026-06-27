<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class AuthService
{
    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return compact('user', 'token');
    }

    public function login(string $email, string $password): ?array
    {
        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return null;
        }

        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return compact('user', 'token');
    }

    /**
     * Find or create a user from a Google profile and issue an access token.
     *
     * @return array{user: User, token: string}
     */
    public function loginWithGoogle(SocialiteUser $googleUser): array
    {
        $user = User::updateOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'google_id' => $googleUser->getId(),
                'name' => $googleUser->getName() ?? $googleUser->getNickname() ?? 'User',
                'avatar' => $googleUser->getAvatar(),
            ]
        );

        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return compact('user', 'token');
    }

    public function logout(PersonalAccessToken $token): void
    {
        $token->delete();
    }
}
