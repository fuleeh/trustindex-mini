<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\ReviewInputDto;
use App\Entity\Review;
use App\Exception\SpamDetectedException;
use App\Form\ReviewType;
use App\Mapper\ReviewMapper;
use App\Repository\ReviewRepository;
use App\Service\ReviewService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;

final class ReviewController extends AbstractController
{
    public function __construct(
        private readonly ReviewRepository $reviewRepository,
        private readonly ReviewService $reviewService,
        private readonly ReviewMapper $reviewMapper,
        private readonly RateLimiterFactory $reviewSubmissionLimiter,
    ) {
    }

    #[Route('/', name: 'review_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->renderIndex($this->createReviewForm());
    }

    #[Route('/reviews', name: 'review_submit', methods: ['POST'])]
    public function submit(Request $request): Response
    {
        $limit = $this->reviewSubmissionLimiter
            ->create($request->getClientIp() ?? 'unknown-client')
            ->consume();

        if (!$limit->isAccepted()) {
            $this->addFlash('warning', 'Túl sok beküldési kísérlet történt. Kérjük, várj egy percet, majd próbáld újra.');
            $response = $this->renderIndex($this->createReviewForm(), Response::HTTP_TOO_MANY_REQUESTS);
            $retryAfter = $limit->getRetryAfter();
            $response->headers->set('Retry-After', (string) max(1, $retryAfter->getTimestamp() - time()));

            return $response;
        }

        $input = new ReviewInputDto();
        $form = $this->createReviewForm($input);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->renderIndex($form, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $this->reviewService->submit($input);
        } catch (SpamDetectedException) {
            $this->addFlash('warning', 'A véleményedet nem tudtuk elfogadni. Ellenőrizd, majd próbáld újra.');

            return $this->redirectToRoute('review_index', status: Response::HTTP_SEE_OTHER);
        }

        $this->addFlash('success', 'Köszönjük a véleményed!');

        return $this->redirectToRoute('review_index', status: Response::HTTP_SEE_OTHER);
    }

    #[Route('/reviews/{id}', name: 'review_detail', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function detail(int $id): Response
    {
        $review = $this->reviewRepository->find($id);

        if (!$review instanceof Review) {
            throw $this->createNotFoundException('A vélemény nem található.');
        }

        return $this->render('review/detail.html.twig', [
            'review' => $this->reviewMapper->toPublicReview($review),
        ]);
    }

    #[Route('/companies', name: 'review_companies', methods: ['GET'])]
    public function companies(): Response
    {
        return $this->render('review/companies.html.twig', [
            'companies' => $this->reviewRepository->getCompanyStats(),
        ]);
    }

    #[Route('/search', name: 'review_search', methods: ['GET'])]
    public function search(Request $request): Response
    {
        $query = mb_substr(trim((string) $request->query->get('q', '')), 0, 255);

        return $this->render('review/search.html.twig', [
            'query' => $query,
            'reviews' => '' === $query
                ? []
                : $this->reviewMapper->toPublicReviews($this->reviewRepository->searchByCompanyName($query)),
        ]);
    }

    /** @return FormInterface<ReviewInputDto> */
    private function createReviewForm(?ReviewInputDto $input = null): FormInterface
    {
        return $this->createForm(ReviewType::class, $input ?? new ReviewInputDto(), [
            'action' => $this->generateUrl('review_submit'),
            'method' => 'POST',
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }

    /** @param FormInterface<ReviewInputDto> $form */
    private function renderIndex(FormInterface $form, int $status = Response::HTTP_OK): Response
    {
        return $this->render('review/index.html.twig', [
            'reviewForm' => $form,
            'reviews' => $this->reviewMapper->toPublicReviews($this->reviewRepository->findAllNewestFirst()),
        ], new Response(status: $status));
    }
}
