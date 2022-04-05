<?php

declare(strict_types=1);

namespace App\Repositories;

use Auth\Entities\PasswordClient;
use Laravel\Passport\Client;

class EloquentClientRepository implements ClientRepositoryInterface
{
    /**
     * The passport client model instance.
     *
     * @var Client
     */
    private $client;

    /**
     * Create a new repository instance.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Find the client by password.
     *
     * @return null|PasswordClient
     */
    public function findPasswordClient(): ?PasswordClient
    {
        $client = $this->client->where('password_client', true)->first();

        return $client ? new PasswordClient($client->id, $client->secret) : null;
    }
}
