<?php

declare(strict_types=1);

namespace App\Repositories;

use Auth\Entities\PasswordClient;

interface ClientRepositoryInterface
{
    /**
     * Find the client by password.
     *
     * @return null|PasswordClient
     */
    public function findPasswordClient(): ?PasswordClient;
}
