<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Support\Collection;
use App\Models\Editor as ModelsEditor;

class EloquentEditorRepository implements EditorRepositoryInterface
{
    private $editor_model;

    public function __construct(ModelsEditor $editor_model)
    {
        $this->editor_model = $editor_model;
    }

    public function save(int $user_id, int $trip_id)
    {
        $this->editor_model->user_id = $user_id;
        $this->editor_model->trip_id = $trip_id;
        $this->editor_model->save();
    }

    public function delete(int $user_id, int $trip_id)
    {
        $this->editor_model->where('user_id', $user_id)
            ->where('trip_id', $trip_id)
            ->delete();
    }

    public function findByTripId(int $trip_id): Collection
    {
        return $this->editor_model->where('trip_id', $trip_id)->get();
    }

    public function findByUserId(int $user_id): Collection
    {
        return $this->editor_model->where('user_id', $user_id)->get();
    }
}
