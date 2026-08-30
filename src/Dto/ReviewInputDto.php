<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class ReviewInputDto
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public ?string $companyName = null;

    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    #[Assert\Range(min: 1, max: 5)]
    public ?int $rating = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 5000)]
    public ?string $reviewText = null;

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 255)]
    public ?string $authorEmail = null;

    public ?string $website = null;

    public function requireCompanyName(): string
    {
        return $this->companyName ?? throw new \LogicException('Company name has not been validated.');
    }

    public function requireRating(): int
    {
        return $this->rating ?? throw new \LogicException('Rating has not been validated.');
    }

    public function requireReviewText(): string
    {
        return $this->reviewText ?? throw new \LogicException('Review text has not been validated.');
    }

    public function requireAuthorEmail(): string
    {
        return $this->authorEmail ?? throw new \LogicException('Author email has not been validated.');
    }
}
