<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Support\Collection;

interface CollectionRepositoryInterface
{
    public function insert(int $trip_id, int $user_id);

    public function delete(int $trip_id, int $user_id);

    public function find(int $user_id): Collection;
}
