<?php

declare(strict_types=1);

namespace Auth\Exceptions;

use App\Exceptions\GeneralException;
use Illuminate\Http\Response;

class ValidationException extends GeneralException
{
    private const PAYLOAD_MAPPING = [
        'email' => 'Email',
        'password' => '密碼',
        'name' => '名稱',
    ];

    /**
     * Create a new exception instance.
     *
     * @param array $payloads
     */
    public function __construct(array $payloads)
    {
        $payloads = collect($payloads)->map(function($payload) {
            return self::PAYLOAD_MAPPING[$payload] ?? $payload;
        })
        ->implode(' ');

        parent::__construct(
            new AuthErrorCode(AuthErrorCode::VALIDATION_FAILED),
            Response::HTTP_UNPROCESSABLE_ENTITY,
            ['payloads' => $payloads]
        );
    }
}
