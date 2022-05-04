<?php

use App\Models\Blocks;
use Illuminate\Support\Collection;

interface BlockRepositoryInterface
{
    public function save(int $user_id, int $block_user_id);

    public function delete(int $user_id, int $block_user_id);

    public function find(int $user_id, int $block_user_id): ?Blocks;

    public function findByUserId(int $user_id): Collection;
}
