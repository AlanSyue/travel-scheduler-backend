<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Support\Collection;

interface UserRepositoryInterface
{
    public function findByEmail(string $email): ?Collection;

    public function create(string $email, string $password, string $name);
}
