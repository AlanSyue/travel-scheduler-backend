<?php

declare(strict_types=1);

namespace App\Exceptions\General;

use Exception;
use ReflectionClass;

class ErrorCode
{
    /**
     * The mapping of error messages.
     *
     * @var array
     */
    protected $messages = [];

    /**
     * The error code.
     *
     * @var string
     */
    private $error_code;

    /**
     * Create a new entity instance.
     *
     * @param string $error_code
     */
    public function __construct(string $error_code)
    {
        if (! in_array($error_code, (new ReflectionClass($this))->getConstants())) {
            throw new Exception('Code Constant Not Found.');
        }
        if (! array_key_exists($error_code, $this->messages)) {
            throw new Exception('Code Message Not Found.');
        }

        $this->error_code = $error_code;
    }

    /**
     * Get the error message.
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->messages[$this->error_code];
    }

    /**
     * Get the error code.
     *
     * @return string
     */
    public function getErrorCode(): string
    {
        return $this->error_code;
    }
}
