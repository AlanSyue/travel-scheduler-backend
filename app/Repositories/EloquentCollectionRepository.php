<?php

declare(strict_types=1);

namespace App\Repositories;

use Trip\Entities\Trip;
use Illuminate\Support\Collection;
use App\Models\Collection as ModelsCollection;

class EloquentCollectionRepository implements CollectionRepositoryInterface
{
    private $model_collection;

    public function __construct(ModelsCollection $model_collection)
    {
        $this->model_collection = $model_collection;
    }

    public function insert(int $trip_id, int $user_id)
    {
        if ($this->model_collection->where('trip_id', $trip_id)->where('user_id', $user_id)->exists()) {
            return;
        }

        $this->model_collection->insert([
            'trip_id' => $trip_id,
            'user_id' => $user_id,
        ]);
    }

    public function delete(int $trip_id, int $user_id)
    {
        $this->model_collection
            ->where('trip_id', $trip_id)
            ->where('user_id', $user_id)
            ->delete();
    }

    public function find(int $user_id): Collection
    {
        return $this->model_collection
            ->where('user_id', $user_id)
            ->get();
    }
}
