<?php

declare(strict_types=1);

namespace Auth\Exceptions;

use App\Exceptions\GeneralException;

class InvalidEmailException extends GeneralException
{
    /**
     * Create a new exception instance.
     */
    public function __construct()
    {
        parent::__construct(new AuthErrorCode(AuthErrorCode::INVALID_EMAIL));
    }
}
