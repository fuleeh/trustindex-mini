<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Dto\ReviewInputDto;
use App\Enum\SpamCheckResult;
use App\Service\SpamChecker;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SpamCheckerTest extends TestCase
{
    private SpamChecker $spamChecker;

    protected function setUp(): void
    {
        $this->spamChecker = new SpamChecker(['online casino', 'free money']);
    }

    public function testCleanReviewIsApproved(): void
    {
        $input = $this->inputWithText('Helpful support and a smooth delivery.');

        self::assertSame(SpamCheckResult::APPROVED, $this->spamChecker->check($input));
    }

    public function testFilledHoneypotIsRejected(): void
    {
        $input = $this->inputWithText('A normal-looking review.');
        $input->website = 'https://spam.example';

        self::assertSame(SpamCheckResult::HONEYPOT, $this->spamChecker->check($input));
    }

    #[DataProvider('spamTextProvider')]
    public function testKeywordMatchingIsCaseInsensitive(string $text): void
    {
        self::assertSame(SpamCheckResult::KEYWORD, $this->spamChecker->check($this->inputWithText($text)));
    }

    /** @return iterable<string, array{string}> */
    public static function spamTextProvider(): iterable
    {
        yield 'lowercase' => ['Try this online casino now'];
        yield 'uppercase' => ['Claim FREE MONEY today'];
    }

    private function inputWithText(string $text): ReviewInputDto
    {
        $input = new ReviewInputDto();
        $input->reviewText = $text;

        return $input;
    }
}
