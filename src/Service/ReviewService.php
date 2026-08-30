<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\ReviewInputDto;
use App\Exception\SpamDetectedException;
use App\Mapper\ReviewMapper;
use App\Repository\ReviewRepository;

final readonly class ReviewService
{
    public function __construct(
        private ReviewRepository $reviewRepository,
        private ReviewMapper $reviewMapper,
        private SpamCheckerInterface $spamChecker,
    ) {
    }

    public function submit(ReviewInputDto $input): void
    {
        $spamResult = $this->spamChecker->check($input);

        if ($spamResult->isSpam()) {
            throw new SpamDetectedException();
        }

        $review = $this->reviewMapper->toEntity($input);
        $this->reviewRepository->save($review);
    }
}
