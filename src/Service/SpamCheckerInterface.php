<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\ReviewInputDto;

interface SpamCheckerInterface
{
    public function check(ReviewInputDto $input): SpamCheckResult;
}
