<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Like as ModelsLike;
use Illuminate\Support\Collection;

class EloquentLikeRepository implements LikeRepositoryInterface
{
    private $like_model;

    public function __construct(ModelsLike $like_model)
    {
        $this->like_model = $like_model;
    }

    public function findByTripId(int $trip_id): Collection
    {
        return $this->like_model->where('trip_id', $trip_id)
            ->with(['user'])
            ->get()
            ->map(function (ModelsLike $like) {
                return $like->user;
            });
    }

    public function save(int $trip_id, int $user_id)
    {
        $this->like_model->trip_id = $trip_id;
        $this->like_model->user_id = $user_id;
        $this->like_model->save();
    }

    public function delete(int $trip_id, int $user_id)
    {
        $this->like_model->where('trip_id', $trip_id)->where('user_id', $user_id)->delete();
    }
}
