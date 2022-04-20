<?php

declare(strict_types=1);

namespace Collection\Services;

use App\Repositories\CollectionRepositoryInterface;
use Trip\Transformer\TripsTransformer;

class GetCollectionsService
{
    private $repo;

    private $transformer;

    public function __construct(CollectionRepositoryInterface $repo, TripsTransformer $transformer)
    {
        $this->repo = $repo;
        $this->transformer = $transformer;
    }

    public function execute(int $user_id): array
    {
        return $this->transformer->transform($this->repo->find($user_id));
    }
}
