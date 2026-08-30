<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Review;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ReviewTest extends TestCase
{
    public function testConstructorSetsReviewDataAndTimestamps(): void
    {
        $review = new Review('Acme', 4, 'A useful review.', 'author@example.com');

        self::assertSame('Acme', $review->getCompanyName());
        self::assertSame(4, $review->getRating());
        self::assertSame('A useful review.', $review->getReviewText());
        self::assertSame('author@example.com', $review->getAuthorEmail());
        self::assertEquals($review->getCreatedAt(), $review->getUpdatedAt());
    }

    public function testRatingOutsideAllowedRangeIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Review('Acme', 6, 'Invalid rating.', 'author@example.com');
    }
}
