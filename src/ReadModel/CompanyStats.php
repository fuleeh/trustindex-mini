<?php

declare(strict_types=1);

namespace App\ReadModel;

final readonly class CompanyStats
{
    public function __construct(
        public string $companyName,
        public int $reviewCount,
        public float $averageRating,
    ) {
    }
}
