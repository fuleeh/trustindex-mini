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
                'companyName' => 'Duna Travel',
                'rating' => 5,
                'reviewText' => 'Átlátható volt a foglalás, és az ügyfélszolgálat gyorsan megoldotta az útiterv módosítását.',
                'authorEmail' => 'alex@example.com',
            ],
            [
                'companyName' => 'Duna Travel',
                'rating' => 5,
                'reviewText' => 'A foglalástól a hazaérkezésig végig pontos és segítőkész tájékoztatást kaptunk.',
                'authorEmail' => 'bianca@example.com',
            ],
            [
                'companyName' => 'Pannon Szoftver',
                'rating' => 5,
                'reviewText' => 'A bevezetés egyszerű volt, a dokumentáció pedig minden felmerülő kérdésünkre választ adott.',
                'authorEmail' => 'chris@example.com',
            ],
            [
                'companyName' => 'Pannon Szoftver',
                'rating' => 4,
                'reviewText' => 'Megbízható terméket kaptunk gyors ügyfélszolgálattal. A kezdeti adatimport lehetne valamivel gyorsabb.',
                'authorEmail' => 'dana@example.com',
            ],
            [
                'companyName' => 'Pannon Szoftver',
                'rating' => 5,
                'reviewText' => 'A csapatunk egyetlen nap alatt megtanulta használni a rendszert. '
                    .'A kezelőfelület logikus, a legfontosabb funkciók könnyen megtalálhatók, és különösen hasznosnak bizonyult a riportnézet. '
                    .'A bevezetés során kapott támogatás gyors és szakmailag alapos volt, ezért más kisebb vállalkozásoknak is nyugodtan ajánlanánk a szolgáltatást.',
                'authorEmail' => 'emil@example.com',
            ],
            [
                'companyName' => 'Zöld Futár',
                'rating' => 4,
                'reviewText' => 'A csomag pontosan érkezett, a nyomkövetési értesítések pedig végig megbízhatók voltak.',
                'authorEmail' => 'farah@example.com',
            ],
            [
                'companyName' => 'Zöld Futár',
                'rating' => 4,
                'reviewText' => 'A futár udvarias volt, a csomagolás pedig újrahasznosítható anyagból készült. '
                    .'Örültem annak is, hogy minden fontos állapotváltozásról időben értesítést kaptam. '
                    .'Egyetlen fejlesztési javaslatom a rövidebb kézbesítési időablak lenne, mert a jelenlegi intervallum mellett nehéz előre megtervezni, mikor kell otthon maradni.',
                'authorEmail' => 'gabriel@example.com',
            ],
            [
                'companyName' => 'Napfény Bank',
                'rating' => 3,
                'reviewText' => 'A mindennapi banki funkciók jól működnek, de az azonosítás a vártnál hosszabb ideig tartott.',
                'authorEmail' => 'hana@example.com',
            ],
            [
                'companyName' => 'Napfény Bank',
                'rating' => 2,
                'reviewText' => 'A mobilalkalmazás igényes, de egy bankkártyával kapcsolatos probléma megoldásához többször is telefonálnom kellett.',
                'authorEmail' => 'ivan@example.com',
            ],
            [
                'companyName' => 'Napfény Bank',
                'rating' => 4,
                'reviewText' => 'A számlanyitás egyszerű volt, a költségekről pedig világos és könnyen érthető tájékoztatást kaptam.',
                'authorEmail' => 'julia@example.com',
            ],
        ];
    }
}
