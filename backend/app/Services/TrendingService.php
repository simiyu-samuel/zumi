<?php

namespace App\Services;

use App\Repositories\Interfaces\WaveRepositoryInterface;
use App\Repositories\Interfaces\ChallengeRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class TrendingService
{
    public function __construct(
        protected WaveRepositoryInterface $waveRepository,
        protected ChallengeRepositoryInterface $challengeRepository
    ) {}

    /**
     * Get aggregated trending data for the platform.
     */
    public function getTrendingData(): array
    {
        return Cache::remember('trending_data', 300, function () {
            return [
                'hashtags'         => $this->getTrendingHashtags(),
                'top_waves'        => $this->getTopWaves(),
                'active_challenge' => $this->getTopActiveChallenge(),
            ];
        });
    }

    /**
     * Get trending hashtags with labels.
     */
    protected function getTrendingHashtags(): array
    {
        $hashtags = $this->waveRepository->getTrendingHashtags(7, 5);
        $trending = [];

        foreach ($hashtags as $tag => $count) {
            $trending[] = [
                'tag'   => '#' . $tag,
                'count' => $count,
                'label' => $count >= 1000 ? round($count / 1000, 1) . 'k posts' : $count . ' posts',
            ];
        }

        // Fallbacks if not enough real data
        if (count($trending) < 3) {
            $defaults = ['SkillDrops', 'WaveChallenge', 'ZumiFlow', 'Creative', 'Trending'];
            foreach ($defaults as $tag) {
                if (count($trending) >= 5) break;
                
                $exists = false;
                foreach ($trending as $t) {
                    if ($t['tag'] === '#' . strtolower($tag)) {
                        $exists = true;
                        break;
                    }
                }

                if (!$exists) {
                    $fakeCount = rand(500, 5000);
                    $trending[] = [
                        'tag'   => '#' . $tag,
                        'count' => $fakeCount,
                        'label' => $fakeCount >= 1000 ? round($fakeCount / 1000, 1) . 'k engagement' : $fakeCount . ' engagement',
                    ];
                }
            }
        }

        return $trending;
    }

    /**
     * Get top performing waves.
     */
    protected function getTopWaves(): array
    {
        $waves = $this->waveRepository->getTopPerforming(7, 5);
        
        return $waves->map(fn ($wave) => [
            'id'          => $wave->id,
            'title'       => $wave->title,
            'views_count' => $wave->views_count,
            'likes_count' => $wave->likes_count,
            'user'        => [
                'name'     => $wave->user->name ?? 'Unknown',
                'username' => $wave->user->username ?? 'unknown',
            ],
        ])->toArray();
    }

    /**
     * Get the top active challenge.
     */
    protected function getTopActiveChallenge(): ?array
    {
        $challenge = $this->challengeRepository->getTopActive();

        if (!$challenge) {
            return null;
        }

        return [
            'id'                   => $challenge->id,
            'title'                => $challenge->title,
            'description'          => $challenge->description,
            'prize_pool'           => $challenge->prize_pool,
            'ends_at'              => $challenge->ends_at,
            'participations_count' => $challenge->participations->count(),
            'user'                 => [
                'name'       => $challenge->user->name,
                'username'   => $challenge->user->username,
                'avatar_url' => $challenge->user->getFirstMediaUrl(\App\Models\User::COLLECTION_AVATAR),
            ],
        ];
    }
}
