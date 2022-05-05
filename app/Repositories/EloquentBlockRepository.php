<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Blocks as ModelsBlock;
use Illuminate\Support\Collection;

class EloquentBlockRepository implements BlockRepositoryInterface
{
    private $block_model;

    public function __construct(ModelsBlock $block_model)
    {
        $this->block_model = $block_model;
    }

    public function save(int $user_id, int $block_user_id)
    {
        $block_model = new ModelsBlock();
        $block_model->user_id = $user_id;
        $block_model->block_user_id = $block_user_id;
        $block_model->save();

        $block_model = new ModelsBlock();
        $block_model->user_id = $block_user_id;
        $block_model->block_user_id = $user_id;
        $block_model->save();
    }

    public function delete(int $user_id, int $block_user_id)
    {
        $this->block_model->where('user_id', $user_id)
            ->where('block_user_id', $block_user_id)
            ->delete();

        $this->block_model->where('user_id', $block_user_id)
            ->where('block_user_id', $user_id)
            ->delete();
    }

    public function find(int $user_id, int $block_user_id): ?ModelsBlock
    {
        return $this->block_model->where('user_id', $user_id)
            ->where('block_user_id', $block_user_id)
            ->first();
    }

    public function findByUserId(int $user_id): Collection
    {
        return $this->block_model->where('user_id', $user_id)
            ->get();
    }
}
