<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Support\Collection;
use App\Models\Friend as ModelsFriend;

class EloquentFriendRepository implements FriendRepositoryInterface
{
    private $friend_model;

    public function __construct(ModelsFriend $friend_model)
    {
        $this->friend_model = $friend_model;
    }

    public function save(int $user_id, int $friend_id, bool $is_active)
    {
        $this->friend_model->user_id = $user_id;
        $this->friend_model->friend_user_id = $friend_id;
        $this->friend_model->is_active = $is_active;
        $this->friend_model->save();
    }

    public function delete(int $user_id, int $friend_id)
    {
        $this->friend_model->where('user_id', $user_id)
            ->where('friend_user_id', $friend_id)
            ->delete();
    }

    public function update(int $user_id, int $friend_id, bool $is_active)
    {
        $this->friend_model->where('user_id', $user_id)
            ->where('friend_user_id', $friend_id)
            ->update(['is_active' => $is_active]);
    }

    public function findMany(int $user_id, ?bool $is_active): Collection
    {
        return $this->friend_model
            ->where('user_id', $user_id)
            ->when($is_active, function($query, $is_active) {
                return $query->where('is_active', $is_active);
            })
            ->get();
    }

    public function findFriends(int $friend_ids, bool $is_active)
    {
        return $this->friend_model->where('friend_user_id', $friend_ids)->where('is_active', $is_active)->get();
    }

    public function findFriend(int $user_id, int $target_user_id): ?ModelsFriend
    {

        return $this->friend_model->where('user_id', $user_id)
            ->where('friend_user_id', $target_user_id)
            ->first();
    }
}
