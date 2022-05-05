<?php

declare(strict_types=1);

namespace Video\Services;

use App\Models\User;
use App\Repositories\BlockRepositoryInterface;
use App\Repositories\FriendRepositoryInterface;
use Illuminate\Support\Collection;
use App\Repositories\VideoRepositoryInterface;

class FindVideosService
{
    private $repo;

    private $friend_repo;

    private $block_repo;

    public function __construct(VideoRepositoryInterface $repo, FriendRepositoryInterface $friend_repo, BlockRepositoryInterface $block_repo)
    {
        $this->repo = $repo;
        $this->friend_repo = $friend_repo;
        $this->block_repo = $block_repo;
    }

    public function execute(?User $user): Collection
    {
        $friends = $user ? $this->friend_repo->findMany($user->id, null) : collect();
        $block_user_ids = $user ? $this->block_repo->findByUserId($user->id)->pluck('block_user_id')->toArray() : [];

        return $this->repo->findMany($friends, $block_user_ids);
    }
}
