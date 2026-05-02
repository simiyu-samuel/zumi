<?php

namespace App\Services;

use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {}

    public function register(array $data)
    {
        $data['password'] = Hash::make($data['password']);

        $user = $this->userRepository->create($data);
        $user->assignRole(\App\Models\User::ROLE_USER);

        return $user;
    }

    public function login(string $email, string $password)
    {
        $user = $this->userRepository->findByEmail($email);

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid login credentials.'],
            ]);
        }

        return $user;
    }

    public function handleSocialCallback(string $provider, $socialUser)
    {
        $idField = $provider . '_id';
        $user = $this->userRepository->findByEmail($socialUser->getEmail());

        if ($user) {
            $user->update([$idField => $socialUser->getId()]);
            return $user;
        }

        $user = $this->userRepository->create([
            'name'                 => $socialUser->getName() ?? $socialUser->getNickname(),
            'email'                => $socialUser->getEmail(),
            'username'             => $this->generateUniqueUsername($socialUser->getNickname() ?? $socialUser->getName()),
            $idField               => $socialUser->getId(),
            'onboarding_completed' => false,
        ]);

        $user->assignRole(\App\Models\User::ROLE_USER);

        return $user;
    }

    protected function generateUniqueUsername(string $name): string
    {
        $base = str($name)->slug('');
        $username = $base;
        $counter = 1;

        while ($this->userRepository->findByUsername($username)) {
            $username = $base . $counter++;
        }

        return $username;
    }
}
