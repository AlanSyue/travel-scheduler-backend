<?php

declare(strict_types=1);

namespace Video\Services;

use App\Models\User;
use App\Repositories\FriendRepositoryInterface;
use Illuminate\Support\Collection;
use App\Repositories\VideoRepositoryInterface;

class FindVideosService
{
    private $repo;

    private $friend_repo;

    public function __construct(VideoRepositoryInterface $repo, FriendRepositoryInterface $friend_repo)
    {
        $this->repo = $repo;
        $this->friend_repo = $friend_repo;
    }

    public function execute(?User $user): Collection
    {
        $friends = $user ? $this->friend_repo->findMany($user->id, null) : collect();

        return $this->repo->findMany($friends);
    }
}
