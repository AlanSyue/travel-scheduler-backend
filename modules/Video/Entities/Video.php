<?php

declare(strict_types=1);

namespace Video\Entities;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

class Video implements Arrayable
{
    private $user;

    private $name;

    private $location;

    private $created_at;

    private $friends;

    public function __construct(User $user, string $name, string $location, Carbon $created_at, Collection $friends)
    {
        $this->user = $user;
        $this->name = $name;
        $this->location = $location;
        $this->created_at = $created_at;
        $this->friends = $friends;
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
            'url' => env('AWS_URL') . $this->name,
            'location' => $this->location,
            'created_at' => $this->created_at->format('Y.m.d'),
        ];
    }
}
