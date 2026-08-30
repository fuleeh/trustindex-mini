<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Review;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        foreach ($this->reviews() as $reviewData) {
            $manager->persist(new Review(...$reviewData));
        }

        $manager->flush();
    }

    /**
     * @return list<array{companyName: string, rating: int, reviewText: string, authorEmail: string}>
     */
    private function reviews(): array
    {
        return [
            [
                'companyName' => 'Alpine Travel',
                'rating' => 5,
                'reviewText' => 'The booking process was clear, and support handled our itinerary change quickly.',
                'authorEmail' => 'alex@example.com',
            ],
            [
                'companyName' => 'Alpine Travel',
                'rating' => 5,
                'reviewText' => 'Excellent communication from booking through to the return journey.',
                'authorEmail' => 'bianca@example.com',
            ],
            [
                'companyName' => 'Trustworthy Tech',
                'rating' => 5,
                'reviewText' => 'Setup was straightforward and the documentation answered every question we had.',
                'authorEmail' => 'chris@example.com',
            ],
            [
                'companyName' => 'Trustworthy Tech',
                'rating' => 4,
                'reviewText' => 'A reliable product with responsive support. The initial import could be faster.',
                'authorEmail' => 'dana@example.com',
            ],
            [
                'companyName' => 'Trustworthy Tech',
                'rating' => 5,
                'reviewText' => 'Our team adopted it in a day, and the reporting view is especially useful.',
                'authorEmail' => 'emil@example.com',
            ],
            [
                'companyName' => 'Green Delivery',
                'rating' => 4,
                'reviewText' => 'The parcel arrived on time and the tracking updates were accurate.',
                'authorEmail' => 'farah@example.com',
            ],
            [
                'companyName' => 'Green Delivery',
                'rating' => 4,
                'reviewText' => 'Friendly courier and recyclable packaging. A smaller delivery window would help.',
                'authorEmail' => 'gabriel@example.com',
            ],
            [
                'companyName' => 'Bright Bank',
                'rating' => 3,
                'reviewText' => 'Everyday banking works well, but the verification process took longer than expected.',
                'authorEmail' => 'hana@example.com',
            ],
            [
                'companyName' => 'Bright Bank',
                'rating' => 2,
                'reviewText' => 'The mobile app is polished, although resolving a card issue required several calls.',
                'authorEmail' => 'ivan@example.com',
            ],
            [
                'companyName' => 'Bright Bank',
                'rating' => 4,
                'reviewText' => 'Account opening was simple and fee information was presented clearly.',
                'authorEmail' => 'julia@example.com',
            ],
        ];
    }
}
