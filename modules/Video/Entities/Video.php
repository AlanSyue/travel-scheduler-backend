<?php

declare(strict_types=1);

namespace Video\Entities;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

class Video implements Arrayable
{
    private $id;

    private $user;

    private $name;

    private $location;

    private $created_at;

    private $friends;

    private $ratings;

    public function __construct(int $id, User $user, string $name, string $location, Carbon $created_at, Collection $friends, Collection $ratings)
    {
        $this->id = $id;
        $this->user = $user;
        $this->name = $name;
        $this->location = $location;
        $this->created_at = $created_at;
        $this->friends = $friends;
        $this->ratings = $ratings;
    }

    public function toArray(): array
    {
        $friend = $this->friends->filter(function ($friend) {
            return $friend->friend_user_id === $this->user->id;
        })
            ->first();

        return [
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'image_url' => $this->user->image_name ? env('AWS_URL') . $this->user->image_name : '',
                'is_friend' => $friend && $friend->is_active ? true : false,
                'is_invite' => $friend && ! $friend->is_active ? true : false,
            ],
            'id' => $this->id,
            'url' => env('AWS_URL') . $this->name,
            'location' => $this->location,
            'ratings' => [
                'total' => $this->ratings->count(),
                'type' => $this->ratings->pluck('type')->unique()->toArray(),
            ],
            'created_at' => $this->created_at->format('Y.m.d'),
        ];
    }
}
