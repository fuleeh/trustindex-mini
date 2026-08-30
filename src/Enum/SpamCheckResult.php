<?php

declare(strict_types=1);

namespace App\Enum;

enum SpamCheckResult: string
{
    case APPROVED = 'approved';
    case HONEYPOT = 'honeypot';
    case KEYWORD = 'keyword';

    public function isSpam(): bool
    {
        return self::APPROVED !== $this;
    }
}
