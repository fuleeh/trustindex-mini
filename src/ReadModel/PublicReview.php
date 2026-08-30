<?php

declare(strict_types=1);

namespace App\ReadModel;

final readonly class PublicReview
{
    public function __construct(
        public int $id,
        public string $companyName,
        public int $rating,
        public string $reviewText,
        public \DateTimeImmutable $createdAt,
    ) {
    }
}
