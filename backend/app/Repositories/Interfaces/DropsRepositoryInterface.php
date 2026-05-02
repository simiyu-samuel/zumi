<?php

namespace App\Repositories\Interfaces;

use App\Models\DropsLedger;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface DropsRepositoryInterface
{
    public function create(array $data): DropsLedger;
    public function getForUser(User $user, int $perPage = 20): LengthAwarePaginator;
    public function calculateBalance(User $user): int;
}
