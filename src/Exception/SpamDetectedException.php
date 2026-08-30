<?php

declare(strict_types=1);

namespace App\Exception;

use App\Service\SpamCheckResult;

final class SpamDetectedException extends \RuntimeException
{
    public function __construct(public readonly SpamCheckResult $result)
    {
        parent::__construct('The review submission was rejected.');
    }
}
