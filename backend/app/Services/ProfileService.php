<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;

class ProfileService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {}

    public function updateProfile(User $user, array $data)
    {
        $this->userRepository->update($user, $data);
        return $user->fresh();
    }

    public function uploadMedia(User $user, $file, string $collection)
    {
        $user->addMedia($file)->toMediaCollection($collection);
        return $user->getFirstMediaUrl($collection);
    }

    public function search(string $query)
    {
        return $this->userRepository->search($query);
    }
}
