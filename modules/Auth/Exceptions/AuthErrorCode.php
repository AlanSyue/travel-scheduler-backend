<?php

declare(strict_types=1);

namespace Auth\Exceptions;

use App\Exceptions\General\ErrorCode;

final class AuthErrorCode extends ErrorCode
{
    public const INVALID_EMAIL = 'AUTH_10001';
    public const INVALID_PASSWORD = 'AUTH_10002';
    public const VALIDATION_FAILED = 'AUTH_10003';

    protected $messages = [
        self::INVALID_EMAIL => 'Email 已經註冊過囉',
        self::INVALID_PASSWORD => '密碼格式錯誤',
        self::VALIDATION_FAILED => ':payloads 資訊錯誤',
    ];
}
