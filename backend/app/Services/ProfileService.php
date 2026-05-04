<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;

class ProfileService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {}

    public function findByUsername(string $username)
    {
        return $this->userRepository->findByUsername($username);
    }

    public function updateProfile(User $user, array $data)
    {
        $this->userRepository->update($user, $data);
        return $user->fresh();
    }

    public function handleOnboarding(User $user, array $data): User
    {
        $updateData = [
            'onboarding_completed' => true,
            'interests'            => $data['interests'] ?? $user->interests,
        ];

        $this->userRepository->update($user, $updateData);

        if (!empty($data['suggested_follows'])) {
            foreach ($data['suggested_follows'] as $followId) {
                $user->following()->syncWithoutDetaching([$followId]);
            }
        }

        return $user->fresh();
    }

    public function uploadMedia(User $user, $file, string $collection)
    {
        $user->addMedia($file)->toMediaCollection($collection);
        return $user->getFirstMediaUrl($collection);
    }

    public function search(string $query, int $perPage = 15)
    {
        return $this->userRepository->search($query, $perPage);
    }

    public function updateNotificationSettings(User $user, array $settings): User
    {
        $currentSettings = $user->notification_settings ?? [];
        $newSettings = array_merge($currentSettings, $settings);

        $this->userRepository->update($user, [
            'notification_settings' => $newSettings,
        ]);

        return $user->fresh();
    }
}
