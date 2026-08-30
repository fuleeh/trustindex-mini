<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Entity\Review;
use App\Repository\ReviewRepository;
use App\Tests\Support\ResetsDatabase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\HttpFoundation\Response;

final class ReviewControllerTest extends WebTestCase
{
    use ResetsDatabase;

    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private ReviewRepository $repository;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->repository = self::getContainer()->get(ReviewRepository::class);
        $this->resetDatabase($this->entityManager);
    }

    public function testIndexPageRendersSuccessfully(): void
    {
        $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Dönts magabiztosan mások tapasztalatai alapján.');
        self::assertSelectorExists('form[name="review"]');
    }

    public function testValidReviewIsPersistedWithoutExposingEmail(): void
    {
        $this->client->submit($this->reviewForm([
            'review[companyName]' => 'Acme',
            'review[rating]' => '5',
            'review[reviewText]' => 'A genuinely excellent experience.',
            'review[authorEmail]' => 'private@example.com',
        ]), serverParameters: ['REMOTE_ADDR' => '192.0.2.10']);

        self::assertResponseStatusCodeSame(Response::HTTP_SEE_OTHER);
        $this->client->followRedirect();
        self::assertSelectorTextContains('.alert-success', 'Köszönjük a véleményed!');
        self::assertSelectorTextContains('.review-card', 'Acme');
        $content = $this->client->getResponse()->getContent();
        self::assertIsString($content);
        self::assertStringNotContainsString('private@example.com', $content);
        self::assertCount(1, $this->repository->findAll());
    }

    public function testInvalidReviewReturnsValidationErrorsWithoutPersistence(): void
    {
        $this->client->submit($this->reviewForm([
            'review[companyName]' => '',
            'review[rating]' => '5',
            'review[reviewText]' => 'Review text.',
            'review[authorEmail]' => 'not-an-email',
        ]), serverParameters: ['REMOTE_ADDR' => '192.0.2.11']);

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertCount(0, $this->repository->findAll());
    }

    public function testSpamKeywordIsRejected(): void
    {
        $this->client->submit($this->reviewForm([
            'review[companyName]' => 'Spam Company',
            'review[rating]' => '5',
            'review[reviewText]' => 'Visit our online casino today.',
            'review[authorEmail]' => 'spam@example.com',
        ]), serverParameters: ['REMOTE_ADDR' => '192.0.2.12']);

        self::assertResponseStatusCodeSame(Response::HTTP_SEE_OTHER);
        $this->client->followRedirect();
        self::assertSelectorExists('.alert-warning');
        self::assertCount(0, $this->repository->findAll());
    }

    public function testDetailPageShowsReviewWithoutExposingEmail(): void
    {
        $acme = new Review('Acme', 4, 'Detailed Acme feedback.', 'one@example.com');
        $this->persistReviews($acme);

        self::assertNotNull($acme->getId());
        $this->client->request('GET', '/reviews/'.$acme->getId());
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.review-body', 'Detailed Acme feedback.');
        $content = $this->client->getResponse()->getContent();
        self::assertIsString($content);
        self::assertStringNotContainsString('one@example.com', $content);
    }

    public function testCompaniesPageOrdersHighestAverageFirst(): void
    {
        $this->persistReviews(
            new Review('Acme', 4, 'Detailed Acme feedback.', 'one@example.com'),
            new Review('Beta', 5, 'Detailed Beta feedback.', 'two@example.com'),
        );

        $this->client->request('GET', '/companies');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('tbody tr:first-child', 'Beta');
    }

    public function testSearchIsCaseInsensitiveAndFiltersOtherCompanies(): void
    {
        $this->persistReviews(
            new Review('Acme', 4, 'Detailed Acme feedback.', 'one@example.com'),
            new Review('Beta', 5, 'Detailed Beta feedback.', 'two@example.com'),
        );

        $this->client->request('GET', '/search?q=acme');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.review-card', 'Acme');
        self::assertSelectorTextNotContains('.card-list', 'Beta');
    }

    private function persistReviews(Review ...$reviews): void
    {
        foreach ($reviews as $review) {
            $this->entityManager->persist($review);
        }

        $this->entityManager->flush();
    }

    /** @param array<string, string> $values */
    private function reviewForm(array $values): Form
    {
        $crawler = $this->client->request('GET', '/');

        return $crawler->selectButton('Vélemény beküldése')->form($values);
    }
}
