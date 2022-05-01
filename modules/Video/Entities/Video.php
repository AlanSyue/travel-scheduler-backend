<?php

declare(strict_types=1);

namespace Video\Entities;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;

class Video implements Arrayable
{
    private $user;

    private $name;

    private $location;

    private $created_at;

    public function __construct(User $user, string $name, string $location, Carbon $created_at)
    {
        $this->user = $user;
        $this->name = $name;
        $this->location = $location;
        $this->created_at = $created_at;
    }

    public function toArray(): array
    {
        return [
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'image_url' => $this->user->image_name ? env('AWS_URL') . $this->user->image_name : '',
            ],
            'url' => env('AWS_URL') . $this->name,
            'location' => $this->location,
            'created_at' => $this->created_at->format('Y-m-d'),
        ];
    }
}
