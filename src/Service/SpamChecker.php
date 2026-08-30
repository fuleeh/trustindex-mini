<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\ReviewInputDto;

final readonly class SpamChecker implements SpamCheckerInterface
{
    /**
     * @param list<string> $keywords
     */
    public function __construct(private array $keywords)
    {
    }

    public function check(ReviewInputDto $input): SpamCheckResult
    {
        if (null !== $input->website && '' !== trim($input->website)) {
            return SpamCheckResult::HONEYPOT;
        }

        $reviewText = $input->reviewText ?? '';

        foreach ($this->keywords as $keyword) {
            if (str_contains(mb_strtolower($reviewText), mb_strtolower($keyword))) {
                return SpamCheckResult::KEYWORD;
            }
        }

        return SpamCheckResult::APPROVED;
    }
}
