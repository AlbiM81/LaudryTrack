<?php

namespace App\Services;

use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {}

    public function login(string $username, string $password): array
    {
        $user = $this->userRepository->findByUsername($username);

        if (! $user || ! $user->is_active || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['Kredensial login tidak valid.'],
            ]);
        }

        $token = $user->createToken('laundrytrack-token')->plainTextToken;

        return [
            'token' => $token,
            'user' => $user,
        ];
    }
}
