<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\ReviewInputDto;
use App\Enum\SpamCheckResult;

interface SpamCheckerInterface
{
    public function check(ReviewInputDto $input): SpamCheckResult;
}
