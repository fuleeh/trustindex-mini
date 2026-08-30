<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Review;
use App\ReadModel\CompanyStats;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Review>
 */
final class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    public function save(Review $review): void
    {
        $this->getEntityManager()->persist($review);
        $this->getEntityManager()->flush();
    }

    /**
     * @return list<Review>
     */
    public function findAllNewestFirst(): array
    {
        return $this->createQueryBuilder('review')
            ->orderBy('review.createdAt', 'DESC')
            ->addOrderBy('review.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<Review>
     */
    public function searchByCompanyName(string $query): array
    {
        return $this->createQueryBuilder('review')
            ->andWhere('LOWER(review.companyName) LIKE :query')
            ->setParameter('query', '%'.mb_strtolower(trim($query)).'%')
            ->orderBy('review.createdAt', 'DESC')
            ->addOrderBy('review.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<CompanyStats>
     */
    public function getCompanyStats(): array
    {
        /** @var list<array{companyName: string, reviewCount: int|string, averageRating: float|string}> $rows */
        $rows = $this->createQueryBuilder('review')
            ->select('review.companyName AS companyName')
            ->addSelect('COUNT(review.id) AS reviewCount')
            ->addSelect('AVG(review.rating) AS averageRating')
            ->groupBy('review.companyName')
            ->orderBy('averageRating', 'DESC')
            ->addOrderBy('review.companyName', 'ASC')
            ->getQuery()
            ->getArrayResult();

        return array_map(
            static fn (array $row): CompanyStats => new CompanyStats(
                companyName: $row['companyName'],
                reviewCount: (int) $row['reviewCount'],
                averageRating: (float) $row['averageRating'],
            ),
            $rows,
        );
    }
}
