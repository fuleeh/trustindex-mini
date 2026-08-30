<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Entity\Review;
use App\Repository\ReviewRepository;
use App\Tests\Support\ResetsDatabase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ReviewRepositoryTest extends KernelTestCase
{
    use ResetsDatabase;

    private EntityManagerInterface $entityManager;
    private ReviewRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->repository = self::getContainer()->get(ReviewRepository::class);
        $this->resetDatabase($this->entityManager);
    }

    public function testCompanyStatsCalculatesAveragesAndSortsDescendingWithStableTies(): void
    {
        $this->persistReviews(
            new Review('Acme', 5, 'Excellent.', 'one@example.com'),
            new Review('Acme', 3, 'Average.', 'two@example.com'),
            new Review('Beta', 5, 'Perfect.', 'three@example.com'),
            new Review('Gamma', 4, 'Good.', 'four@example.com'),
        );

        $stats = $this->repository->getCompanyStats();

        self::assertSame(['Beta', 'Acme', 'Gamma'], array_column($stats, 'companyName'));
        self::assertSame([1, 2, 1], array_column($stats, 'reviewCount'));
        self::assertSame([5.0, 4.0, 4.0], array_column($stats, 'averageRating'));
    }

    private function persistReviews(Review ...$reviews): void
    {
        foreach ($reviews as $review) {
            $this->entityManager->persist($review);
        }

        $this->entityManager->flush();
    }
}
