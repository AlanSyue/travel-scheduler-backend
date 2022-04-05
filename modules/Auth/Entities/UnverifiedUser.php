<?php

declare(strict_types=1);

namespace Auth\Entities;

use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class UnverifiedUser
{
    use Notifiable;

    /**
     * The user name.
     *
     * @var string
     */
    private $name;

    /**
     * The user email address.
     *
     * @var string
     */
    private $email;

    public function __construct(string $name, string $email)
    {
        $this->name = $name;
        $this->email = $email;
    }

    /**
     * Get the recipients.
     *
     * @return Collection
     */
    public function recipients(): Collection
    {
        return collect([['name' => $this->name, 'email' => $this->email]]);
    }
}
