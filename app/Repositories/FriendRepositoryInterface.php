<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Friend;
use Illuminate\Support\Collection;

interface FriendRepositoryInterface
{
    public function save(int $user_id, int $friend_id, bool $is_active);

    public function delete(int $user_id, int $friend_id);

    public function update(int $user_id, int $friend_id, bool $is_active);

    public function findMany(int $user_id, ?bool $is_active): Collection;

    public function findFriends(int $friend_ids, bool $is_active);

    public function findFriend(int $user_id, int $target_user_id): ?Friend;
}
