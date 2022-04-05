<?php

declare(strict_types=1);

namespace Auth\Dtos;

use Illuminate\Support\Facades\Hash;

class AuthDto
{
    /**
     * The user email.
     *
     * @var string
     */
    private $email;

    /**
     * The user password.
     *
     * @var string
     */
    private $password;

    /**
     * The user name.
     *
     * @var string
     */
    private $name;

    /**
     * The auth driver.
     *
     * @var string
     */
    private $driver;

    /**
     * Create a DTO instance.
     *
     * @param string $email
     * @param string $password
     * @param string $name
     * @param string $driver
     */
    public function __construct(string $email, string $password, string $name, string $driver)
    {
        $this->email = $email;
        $this->password = $password;
        $this->name = $name;
        $this->driver = $driver;
    }

    /**
     * Get the value of email
     *
     * @var string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Get the value of password
     *
     * @var string
     */
    public function getPassword(): string
    {
        return Hash::make($this->password);
    }

    /**
     * Get the value of name
     *
     * @var string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the value of driver
     *
     * @var string
     */
    public function getDriver(): string
    {
        return $this->driver;
    }
}
