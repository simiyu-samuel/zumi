<?php

namespace App\Repositories\Eloquent;

use App\Models\DropsLedger;
use App\Models\User;
use App\Repositories\Interfaces\DropsRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentDropsRepository implements DropsRepositoryInterface
{
    public function create(array $data): DropsLedger
    {
        return DropsLedger::create($data);
    }

    public function getForUser(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return DropsLedger::where('user_id', $user->id)
            ->latest('created_at')
            ->paginate($perPage);
    }

    public function calculateBalance(User $user): int
    {
        $credits = DropsLedger::where('user_id', $user->id)
            ->where('direction', DropsLedger::DIRECTION_CREDIT)
            ->where('status', DropsLedger::STATUS_COMPLETED)
            ->sum('amount');

        $debits = DropsLedger::where('user_id', $user->id)
            ->where('direction', DropsLedger::DIRECTION_DEBIT)
            ->where('status', DropsLedger::STATUS_COMPLETED)
            ->sum('amount');

        return $credits - $debits;
    }
}
