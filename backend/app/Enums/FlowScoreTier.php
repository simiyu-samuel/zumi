<?php

namespace App\Enums;

enum FlowScoreTier: string
{
    case Rising  = 'rising';
    case Flowing = 'flowing';
    case Surging = 'surging';
    case Elite   = 'elite';

    /**
     * Resolve tier from a raw Flow Score value.
     */
    public static function fromScore(int $score): self
    {
        $tiers = config('zumi.flow_score.tiers');

        if ($score >= ($tiers['elite']['threshold'] ?? 15000)) return self::Elite;
        if ($score >= ($tiers['surging']['threshold'] ?? 5000)) return self::Surging;
        if ($score >= ($tiers['flowing']['threshold'] ?? 1000)) return self::Flowing;
        
        return self::Rising;
    }

    public function label(): string
    {
        return config("zumi.flow_score.tiers.{$this->value}.label") ?? ucfirst($this->value);
    }

    public function nextThreshold(): ?int
    {
        $tiers = config('zumi.flow_score.tiers');

        return match ($this) {
            self::Rising  => $tiers['flowing']['threshold'] ?? 1000,
            self::Flowing => $tiers['surging']['threshold'] ?? 5000,
            self::Surging => $tiers['elite']['threshold'] ?? 15000,
            self::Elite   => null,
        };
    }
}
