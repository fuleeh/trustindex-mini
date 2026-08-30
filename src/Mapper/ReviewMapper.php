<?php

declare(strict_types=1);

namespace App\Mapper;

use App\Dto\ReviewInputDto;
use App\Entity\Review;

final readonly class ReviewMapper
{
    public function toEntity(ReviewInputDto $input): Review
    {
        $companyName = trim($input->requireCompanyName());

        return new Review(
            companyName: preg_replace('/\s+/u', ' ', $companyName) ?? $companyName,
            rating: $input->requireRating(),
            reviewText: trim($input->requireReviewText()),
            authorEmail: mb_strtolower(trim($input->requireAuthorEmail())),
        );
    }
}
