<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Exceptions\General\ErrorCode;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class GeneralException extends HttpResponseException
{
    /**
     * Create a new exception instance.
     *
     * @param ErrorCode $$code
     * @param int       $http_code
     * @param array     $vars
     */
    public function __construct(ErrorCode $code, $http_code = Response::HTTP_BAD_REQUEST, $vars = [])
    {
        parent::__construct(response()->json([
            'error_code' => $code->getErrorCode(),
            'error_message' => $this->buildErrorMessage($code, $vars),
        ], $http_code));
    }

    /**
     * Build the error message.
     *
     * @param ErrorCode $code
     * @param array     $vars
     *
     * @return string
     */
    private function buildErrorMessage(ErrorCode $code, array $vars): string
    {
        $message = $code->getMessage();

        foreach ($vars as $key => $value) {
            $message = str_replace(":{$key}", $value, $message);
        }

        return $message;
    }
}
