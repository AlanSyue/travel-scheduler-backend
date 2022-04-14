<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Support\Collection;

interface TripRepositoryInterface
{
    public function findByUserId(int $user_id): Collection;
}
