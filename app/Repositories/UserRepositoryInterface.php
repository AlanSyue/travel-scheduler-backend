<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Collection;

interface UserRepositoryInterface
{
    public function findByEmail(string $email): ?Collection;

    public function create(string $email, string $password, string $name);

    public function find(int $user_id): ?User;
}
