<?php

declare(strict_types=1);

namespace Collection\Services;

use App\Repositories\CollectionRepositoryInterface;

class SwitchCollectionService
{
    private $repo;

    public function __construct(CollectionRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $user_id, int $trip_id, bool $is_collected)
    {
        if ($is_collected) {
            $this->repo->insert($trip_id, $user_id);
        } else {
            $this->repo->delete($trip_id, $user_id);
        }
    }
}
