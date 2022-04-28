<?php

declare(strict_types=1);

namespace App\Repositories;

interface FriendRepositoryInterface
{
    public function save(int $user_id, int $friend_id, bool $is_active);

    public function delete(int $user_id, int $friend_id);

    public function update(int $user_id, int $friend_id, bool $is_active);

    public function findMany(int $user_id, bool $is_active);

    public function findFriends(int $friend_ids, bool $is_active);
}
