<?php

declare(strict_types=1);

namespace Video\Entities;

use App\Models\User;
use Illuminate\Contracts\Support\Arrayable;

class Video implements Arrayable
{
    private $user;

    private $name;

    public function __construct(User $user, string $name)
    {
        $this->user = $user;
        $this->name = $name;
    }

    public function toArray(): array
    {
        return [
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'image_url' => '',
            ],
            'name' => env('AWS_URL') . $this->name,
        ];
    }
}
