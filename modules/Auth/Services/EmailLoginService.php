<?php

declare(strict_types=1);

namespace Auth\Services;

use App\Repositories\ClientRepositoryInterface;
use Auth\Clients\PassportClient;
use Exception;
use Illuminate\Http\Response;

class EmailLoginService
{
    /**
     * The client repository instance.
     *
     * @var ClientRepositoryInterface
     */
    private $repo;

    /**
     * The passport client instance.
     *
     * @var PassportClient
     */
    private $client;

    /**
     * Create a new service instance.
     *
     * @param ClientRepositoryInterface $repo
     * @param PassportClient            $client
     */
    public function __construct(ClientRepositoryInterface $repo, PassportClient $client)
    {
        $this->repo = $repo;
        $this->client = $client;
    }

    /**
     * Execute the service.
     *
     * @param string $email
     * @param string $password
     *
     * @return Response
     */
    public function execute(string $email, string $password): Response
    {
        $password_client = $this->repo->findPasswordClient();

        if (! $password_client) {
            throw new Exception('Service error', 1);
        }

        return $this->client->getTokenByPasswordClient($email, $password, $password_client);
    }
}
