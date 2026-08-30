<?php

declare(strict_types=1);

namespace App\Mapper;

use App\Dto\ReviewInputDto;
use App\Entity\Review;
use App\ReadModel\PublicReview;

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

    public function toPublicReview(Review $review): PublicReview
    {
        $id = $review->getId();

        if (null === $id) {
            throw new \LogicException('A review must be persisted before it can be displayed.');
        }

        return new PublicReview(
            id: $id,
            companyName: $review->getCompanyName(),
            rating: $review->getRating(),
            reviewText: $review->getReviewText(),
            createdAt: $review->getCreatedAt(),
        );
    }

    /**
     * @param iterable<Review> $reviews
     *
     * @return list<PublicReview>
     */
    public function toPublicReviews(iterable $reviews): array
    {
        $publicReviews = [];

        foreach ($reviews as $review) {
            $publicReviews[] = $this->toPublicReview($review);
        }

        return $publicReviews;
    }
}
