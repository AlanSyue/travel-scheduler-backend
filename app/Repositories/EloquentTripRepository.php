<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Collection as ModelsCollection;
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

    private $collection_model;

    /**
     * Create a new repository instance.
     *
     * @param ModelsTrip $trip_model
     * @param ModelsCollection $collection_model
     */
    public function __construct(ModelsTrip $trip_model, ModelsCollection $collection_model)
    {
        $this->trip_model = $trip_model;
        $this->collection_model = $collection_model;
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
        $collection_trip_ids = $user_id ? $this->collection_model->where('user_id', $user_id)->get()->pluck('trip_id')->toArray() : [];

        return $this->trip_model
            ->with(['user'])
            ->where('user_id', $user_id)
            ->where('is_published', false)
            ->get()
            ->transform(function (ModelsTrip $trip) use ($collection_trip_ids) {
                $is_collected = in_array($trip->id, $collection_trip_ids) ? true : false;

                return (new Trip($trip->id, $trip->user, $trip->title, $trip->start_at, $trip->end_at, $is_collected))->toArray();
            });
    }

    /**
     * Find by is published column.
     *
     * @param bool $is_published
     * @param int|null $user_id
     *
     * @return Collection
     */
    public function findByIsPublished(bool $is_published, ?int $user_id = null): Collection
    {
        $collection_trip_ids = $user_id ? $this->collection_model->where('user_id', $user_id)->get()->pluck('trip_id')->toArray() : [];

        return $this->trip_model
            ->with(['user'])
            ->where('is_published', $is_published)
            ->orderBy('updated_at', 'desc')
            ->get()
            ->transform(function (ModelsTrip $trip) use ($collection_trip_ids) {
                $is_collected = in_array($trip->id, $collection_trip_ids) ? true : false;

                return (new Trip($trip->id, $trip->user, $trip->title, $trip->start_at, $trip->end_at, $is_collected))->toArray();
            });
    }

    /**
     * Find the trip.
     *
     * @param int $trip_id
     *
     * @return null|Trip
     */
    public function find(int $trip_id): ?Trip
    {
        $trip = $this->trip_model->where('id', $trip_id)->first();

        return $trip ? new Trip($trip->id, $trip->user, $trip->title, $trip->start_at, $trip->end_at) : null;
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
            'end_at' => $trip->getEndAt(),
        ]);
    }

    public function update(int $trip_id, array $update_data)
    {
        $this->trip_model->where('id', $trip_id)->update($update_data);
    }
}
