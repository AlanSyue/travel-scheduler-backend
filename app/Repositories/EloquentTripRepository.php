<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Trip as ModelsTrip;
use Illuminate\Support\Collection;
use Trip\Entities\Trip;

class EloquentTripRepository implements TripRepositoryInterface
{
    /**
     * The trip model instance.
     *
     * @var ModelsTrip
     */
    private $trip_model;

    /**
     * Create a new repository instance.
     *
     * @param ModelsTrip $trip_model
     */
    public function __construct(ModelsTrip $trip_model)
    {
        $this->trip_model = $trip_model;
    }

    /**
     * Find by user ID.
     *
     * @param int $user_id
     *
     * @return Trip[]
     */
    public function findByUserId(int $user_id): Collection
    {
        return $this->trip_model
            ->where('user_id', $user_id)
            ->get()
            ->transform(function (ModelsTrip $trip) use ($user_id) {
                return (new Trip($trip->id, $user_id, $trip->title, $trip->start_at, $trip->end_at, $trip->editors))->toArray();
            });
    }

    /**
     * Insert and get ID.
     *
     * @param Trip $trip
     *
     * @return int
     */
    public function insertGetId(Trip $trip): int
    {
        return $this->trip_model->insertGetId([
            'title' => $trip->getTitle(),
            'user_id' => $trip->getUserId(),
            'start_at' => $trip->getStartAt(),
            'end_at' => $trip->getEndAt()
        ]);
    }
}
