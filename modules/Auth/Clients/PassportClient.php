<?php

declare(strict_types=1);

namespace Auth\Clients;

use Auth\Entities\PasswordClient;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PassportClient
{
    /**
     * The HTTP request instance.
     *
     * @var Request
     */
    private $request;

    /**
     * Create a new client instance.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Get the token by password client.
     *
     * @param string         $email
     * @param string         $password
     * @param PasswordClient $password_client
     *
     * @return Response
     */
    public function getTokenByPasswordClient(
        string $email,
        string $password,
        PasswordClient $password_client
    ): Response {
        $auth_request = $this->request::create(
            route('passport.token'),
            Request::METHOD_POST,
            [
                'grant_type' => $password_client->type(),
                'client_id' => $password_client->id(),
                'client_secret' => $password_client->secret(),
                'username' => $email,
                'password' => $password,
            ]
        );

        return app()->handle($auth_request);
    }
}
