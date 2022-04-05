<?php

declare(strict_types=1);

namespace Auth\Entities;

class PasswordClient
{
    /**
     * The grant type.
     *
     * @var string
     */
    private const TYPE = 'password';

    /**
     * The client id.
     *
     * @var string
     */
    private $id;

    /**
     * The client secret.
     *
     * @var string
     */
    private $secret;

    /**
     * Create a new entity instance.
     *
     * @param string $id
     * @param string $secret
     */
    public function __construct(string $id, string $secret)
    {
        $this->id = $id;
        $this->secret = $secret;
    }

    /**
     * Get the grant type.
     *
     * @return string
     */
    public function type(): string
    {
        return self::TYPE;
    }

    /**
     * Get the client id.
     *
     * @return string
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * Get the client secret.
     *
     * @return string
     */
    public function secret(): string
    {
        return $this->secret;
    }
}
