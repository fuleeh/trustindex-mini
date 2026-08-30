<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class ReviewInputDto
{
    #[Assert\NotBlank(message: 'A cégnév megadása kötelező.')]
    #[Assert\Length(max: 255, maxMessage: 'A cégnév legfeljebb {{ limit }} karakter lehet.')]
    public ?string $companyName = null;

    #[Assert\NotBlank(message: 'Az értékelés megadása kötelező.')]
    #[Assert\Type(type: 'integer', message: 'Az értékelésnek egész számnak kell lennie.')]
    #[Assert\Range(min: 1, max: 5, notInRangeMessage: 'Az értékelésnek {{ min }} és {{ max }} között kell lennie.')]
    public ?int $rating = null;

    #[Assert\NotBlank(message: 'A vélemény megadása kötelező.')]
    #[Assert\Length(max: 5000, maxMessage: 'A vélemény legfeljebb {{ limit }} karakter lehet.')]
    public ?string $reviewText = null;

    #[Assert\NotBlank(message: 'Az e-mail-cím megadása kötelező.')]
    #[Assert\Email(message: 'Adj meg egy érvényes e-mail-címet.')]
    #[Assert\Length(max: 255, maxMessage: 'Az e-mail-cím legfeljebb {{ limit }} karakter lehet.')]
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
