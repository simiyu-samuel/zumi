<?php

namespace App\Services;

use App\Repositories\Interfaces\WaveRepositoryInterface;

class WaveService
{
    public function __construct(
        protected WaveRepositoryInterface $waveRepository
    ) {}

    public function getFeed(string $type = 'random')
    {
        return $this->waveRepository->getFeed($type);
    }

    public function createWave(array $data)
    {
        return $this->waveRepository->create($data);
    }

    public function toggleLike(int $waveId, int $userId)
    {
        $wave = $this->waveRepository->findById($waveId);
        
        if (!$wave) {
            return null;
        }

        return $this->waveRepository->toggleLike($wave, $userId);
    }
}
