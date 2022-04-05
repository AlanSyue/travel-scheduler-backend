<?php

declare(strict_types=1);

namespace Auth\Enums;

enum AuthDriver: string
{
    case Email = 'email';

    case Apple = 'apple';
}
