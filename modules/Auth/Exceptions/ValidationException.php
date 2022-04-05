<?php

declare(strict_types=1);

namespace Auth\Exceptions;

use App\Exceptions\GeneralException;
use Illuminate\Http\Response;

class ValidationException extends GeneralException
{
    /**
     * Create a new exception instance.
     *
     * @param array $payloads
     */
    public function __construct(array $payloads)
    {
        parent::__construct(
            new AuthErrorCode(AuthErrorCode::VALIDATION_FAILED),
            Response::HTTP_UNPROCESSABLE_ENTITY,
            ['payloads' => implode(',', $payloads)]
        );
    }
}
