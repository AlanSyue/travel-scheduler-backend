<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Collection as ModelsCollection;
use App\Models\Editor as ModelsEditor;
use App\Models\Like as ModelsLike;
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

    private $like_model;

    private $editor_model;

    private $block_repo;

    /**
     * Create a new repository instance.
     *
     * @param ModelsTrip               $trip_model
     * @param ModelsCollection         $collection_model
     * @param ModelsLike               $like_model
     * @param ModelsEditor             $editor_model
     * @param BlockRepositoryInterface $block_repo
     */
    public function __construct(
        ModelsTrip $trip_model,
        ModelsCollection $collection_model,
        ModelsLike $like_model,
        ModelsEditor $editor_model,
        BlockRepositoryInterface $block_repo
    ) {
        $this->trip_model = $trip_model;
        $this->collection_model = $collection_model;
        $this->like_model = $like_model;
        $this->editor_model = $editor_model;
        $this->block_repo = $block_repo;
    }

    /**
     * Find by user ID.
     *
     * @param null|int $user_id
     *
     * @return Trip[]
     */
    public function findByUserId(?int $user_id): Collection
    {
        $collection_trip_ids = $user_id ? $this->collection_model->where('user_id', $user_id)->get()->pluck('trip_id')->toArray() : [];
        $like_trip_ids = $user_id ? $this->like_model->where('user_id', $user_id)->get()->pluck('trip_id')->toArray() : [];
        $block_user_ids = $user_id ? $this->block_repo->findByUserId($user_id)->pluck('block_user_id')->toArray() : [];

        return $this->trip_model
            ->with(['user'])
            ->where('user_id', $user_id)
            ->where('is_published', false)
            ->get()
            ->transform(function (ModelsTrip $trip) use ($collection_trip_ids, $like_trip_ids, $block_user_ids) {
                $owner = $trip->user;
                if (in_array($owner->id, $block_user_ids)) {
                    return;
                }
                $is_collected = in_array($trip->id, $collection_trip_ids) ? true : false;
                $is_liked = in_array($trip->id, $like_trip_ids) ? true : false;
                $editors = $this->editor_model
                    ->with(['user'])
                    ->where('trip_id', $trip->id)
                    ->get();

                return (new Trip($trip->id, $owner, $trip->title, $trip->start_at, $trip->end_at, $trip->is_published, $trip->is_private, $editors, $trip->updated_at, $is_collected, $is_liked))->toArray();
            })
            ->reject(function ($trip) {
                return ! $trip;
            });
    }

    /**
     * Find by is published column.
     *
     * @param bool     $is_published
     * @param null|int $user_id
     * @param null|int $filter_user_id
     *
     * @return Collection
     */
    public function findByIsPublished(
        bool $is_published,
        bool $filter_is_private,
        bool $is_private = false,
        ?int $user_id = null,
        ?int $filter_user_id = null
    ): Collection {
        $collection_trip_ids = $user_id ? $this->collection_model->where('user_id', $user_id)->get()->pluck('trip_id')->toArray() : [];
        $like_trip_ids = $user_id ? $this->like_model->where('user_id', $user_id)->get()->pluck('trip_id')->toArray() : [];
        $block_user_ids = $user_id ? $this->block_repo->findByUserId($user_id)->pluck('block_user_id')->toArray() : [];

        return $this->trip_model
            ->with(['user', 'likes', 'comments'])
            ->where('is_published', $is_published)
            ->when($filter_is_private, function ($query) use ($is_private) {
                return $query->where('is_private', $is_private);
            })
            ->when($filter_user_id, function ($query, $filter_user_id) {
                return $query->where('user_id', $filter_user_id);
            })
            ->orderBy('updated_at', 'desc')
            ->get()
            ->transform(function (ModelsTrip $trip_model) use ($collection_trip_ids, $like_trip_ids, $block_user_ids) {
                $owner = $trip_model->user;
                if (in_array($owner->id, $block_user_ids)) {
                    return;
                }

                $is_collected = in_array($trip_model->id, $collection_trip_ids) ? true : false;
                $is_liked = in_array($trip_model->id, $like_trip_ids) ? true : false;
                $editors = $this->editor_model
                    ->with(['user'])
                    ->where('trip_id', $trip_model->id)
                    ->get();

                $trip = (new Trip(
                    $trip_model->id,
                    $owner,
                    $trip_model->title,
                    $trip_model->start_at,
                    $trip_model->end_at,
                    $trip_model->is_published,
                    $trip_model->is_private,
                    $editors,
                    $trip_model->updated_at,
                    $is_collected,
                    $is_liked
                ))
                    ->setLikesCount($trip_model->likes->count())
                    ->setCommentsCount($trip_model->comments->count());

                return $trip->toArray();
            })
            ->reject(function ($trip) {
                return ! $trip;
            })
            ->values();
    }

    /**
     * Find the trip.
     *
     * @param int $trip_id
     *
     * @return null|Trip
     */
    public function find(int $trip_id, ?int $user_id = null): ?Trip
    {
        $trip = $this->trip_model->where('id', $trip_id)->first();

        if (! $trip) {
            return null;
        }

        $block_user_ids = $this->block_repo->findByUserId($trip->user_id)->pluck('block_user_id')->toArray();

        $collection_trip_ids = $user_id ? $this->collection_model->where('user_id', $user_id)->get()->pluck('trip_id')->toArray() : [];
        $is_collected = in_array($trip_id, $collection_trip_ids) ? true : false;

        $like_trip_ids = $user_id ? $this->like_model->where('user_id', $user_id)->get()->pluck('trip_id')->toArray() : [];
        $is_liked = in_array($trip_id, $like_trip_ids) ? true : false;

        $editors = $trip
            ? $this->editor_model
                ->with(['user'])
                ->where('trip_id', $trip->id)
                ->when($block_user_ids, function ($query, $block_user_ids) {
                    return $query->whereNotIn('user_id', $block_user_ids);
                })
                ->get()
            : collect();

        return $trip
            ? (new Trip(
                $trip->id,
                $trip->user,
                $trip->title,
                $trip->start_at,
                $trip->end_at,
                $trip->is_published,
                $trip->is_private,
                $editors,
                $trip->updated_at,
                $is_collected,
                $is_liked
            ))
                ->setLikesCount($trip->likes->count())
                ->setCommentsCount($trip->comments->count())
            : null;
    }

    public function findMany(array $trip_ids, ?int $user_id = null, bool $is_published = true, bool $is_private = false): Collection
    {
        $collection_trip_ids = $user_id ? $this->collection_model->where('user_id', $user_id)->get()->pluck('trip_id')->toArray() : [];
        $like_trip_ids = $user_id ? $this->like_model->where('user_id', $user_id)->get()->pluck('trip_id')->toArray() : [];

        return $this->trip_model
            ->with(['user'])
            ->whereIn('id', $trip_ids)
            ->where('is_published', $is_published)
            ->where('is_private', $is_private)
            ->orderBy('updated_at', 'desc')
            ->get()
            ->transform(function (ModelsTrip $trip_model) use ($collection_trip_ids, $like_trip_ids) {
                $is_collected = in_array($trip_model->id, $collection_trip_ids) ? true : false;
                $is_liked = in_array($trip_model->id, $like_trip_ids) ? true : false;
                $editors = $this->editor_model
                    ->with(['user'])
                    ->where('trip_id', $trip_model->id)
                    ->get();

                $trip = (new Trip(
                    $trip_model->id,
                    $trip_model->user,
                    $trip_model->title,
                    $trip_model->start_at,
                    $trip_model->end_at,
                    $trip_model->is_published,
                    $trip_model->is_private,
                    $editors,
                    $trip_model->updated_at,
                    $is_collected,
                    $is_liked
                ))
                    ->setLikesCount($trip_model->likes->count())
                    ->setCommentsCount($trip_model->comments->count());

                return $trip->toArray();
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
            'end_at' => $trip->getEndAt(),
        ]);
    }

    public function update(int $trip_id, array $update_data)
    {
        $this->trip_model->where('id', $trip_id)->update($update_data);
    }
}
