<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Collection;

class EloquentUserRepository implements UserRepositoryInterface
{
    /**
     * The user model instance.
     *
     * @var User
     */
    private $user;

    /**
     * Create a new repository instance.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Find user by email.
     *
     * @param string $email
     *
     * @return null|User
     */
    public function findByEmail(string $email): ?User
    {
        return $this->user->where('email', $email)->first();
    }

    /**
     * Create a new user.
     *
     * @param string $email
     * @param string $password
     * @param string $name
     *
     * @return void
     */
    public function create(string $email, string $password, string $name)
    {
        $this->user->create([
            'email' => $email,
            'password' => $password,
            'name' => $name,
        ]);
    }

    public function find(int $user_id): ?User
    {
        return $this->user->find($user_id);
    }

    public function findMany(array $user_ids)
    {
        return $this->user->whereIn('id', $user_ids)->get();
    }
}
