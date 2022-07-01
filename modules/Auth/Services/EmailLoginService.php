<?php

declare(strict_types=1);

namespace Auth\Services;

use App\Repositories\ClientRepositoryInterface;
use App\Repositories\UserRepositoryInterface;
use Auth\Clients\PassportClient;
use Exception;

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

    private $user_repo;

    /**
     * Create a new service instance.
     *
     * @param ClientRepositoryInterface $repo
     * @param PassportClient            $client
     * @param UserRepositoryInterface   $user_repo
     */
    public function __construct(ClientRepositoryInterface $repo, PassportClient $client, UserRepositoryInterface $user_repo)
    {
        $this->repo = $repo;
        $this->client = $client;
        $this->user_repo = $user_repo;
    }

    /**
     * Execute the service.
     *
     * @param string $email
     * @param string $password
     *
     * @return array
     */
    public function execute(string $email, string $password): array
    {
        $password_client = $this->repo->findPasswordClient();

        if (! $password_client) {
            throw new Exception('Service error', 1);
        }

        $json = $this->client->getTokenByPasswordClient($email, $password, $password_client)->getContent();
        $result = json_decode($json, true);

        if (isset($result['error'])) {
            throw new Exception('Login error', 1);
        }

        $user = $this->user_repo->findByEmail($email);

        $result['user_id'] = $user->id;

        return $result;
    }
}
