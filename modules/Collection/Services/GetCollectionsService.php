<?php

declare(strict_types=1);

namespace Collection\Services;

use App\Repositories\CollectionRepositoryInterface;
use App\Repositories\TripRepositoryInterface;
use Trip\Transformer\TripsTransformer;

class GetCollectionsService
{
    private $repo;

    private $trip_repo;

    private $transformer;

    public function __construct(CollectionRepositoryInterface $repo, TripRepositoryInterface $trip_repo, TripsTransformer $transformer)
    {
        $this->repo = $repo;
        $this->trip_repo = $trip_repo;
        $this->transformer = $transformer;
    }

    public function execute(int $user_id): array
    {
        $collection_trip_ids = $this->repo->find($user_id)->pluck('trip_id')->toArray();

        $trips = $this->trip_repo->findMany($collection_trip_ids, $user_id);

        return $this->transformer->transform($trips);
    }
}
