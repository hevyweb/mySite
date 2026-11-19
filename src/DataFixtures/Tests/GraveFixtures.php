<?php

namespace App\DataFixtures\Tests;

use App\Entity\Grave;
use App\Type\Gender;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class GraveFixtures extends Fixture implements FixtureGroupInterface
{
    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $grave = new Grave();

        $grave
            ->setFirstName('Michael')
            ->setLastName('Jackson')
            ->setMaidenName('Joseph')
            ->setBirthDate(\DateTime::createFromFormat('d.m.Y', '29.08.1958'))
            ->setDeathDate(\DateTime::createFromFormat('d.m.Y', '25.06.2009'))
            ->setImage('no-image.png')
            ->setBiography('He began career which the Jackson 5 before selling solo')
            ->setBirthPlace('Garry, Indiana')
            ->setDeathPlace('Los Angeles, California')
            ->setCreatedAt(\DateTimeImmutable::createFromFormat('d.m.Y', '22.02.2022'))
            ->setUpdatedAt(\DateTime::createFromFormat('d.m.Y', '09.11.2026'))
            ->setBirthPlaceCoordinates('41.5934, -87.3464')
            ->setDeathPlaceCoordinates('34.0707, -118.4237')
            ->setEpitaph('A legacy written in rhythm forever the King of Pop')
            ->setGender(Gender::MALE)
            ->setGravePlace('Glendale, California')
            ->setGravePlaceCoordinates('-34.125, -118.2403')
            ->setIsPublished(true)
        ;

        $manager->persist($grave);
        $this->addReference('grave', $grave);

        $grave2 = new Grave();

        $grave2
            ->setFirstName('Freddie')
            ->setLastName('Mercury')
            ->setMaidenName('Bulsara')
            ->setBirthDate(\DateTime::createFromFormat('d.m.Y', '05.09.1946'))
            ->setDeathDate(\DateTime::createFromFormat('d.m.Y', '24.11.1991'))
            ->setImage('no-image.png')
            ->setBiography('Legendary lead singer of the rock band Queen')
            ->setBirthPlace('Stone Town, Zanzibar')
            ->setDeathPlace('Kensington, London')
            ->setCreatedAt(\DateTimeImmutable::createFromFormat('d.m.Y', '22.02.2022'))
            ->setUpdatedAt(\DateTime::createFromFormat('d.m.Y', '09.11.2026'))
            ->setBirthPlaceCoordinates('-6.1659, 39.2026')
            ->setDeathPlaceCoordinates('51.4994, -0.1948')
            ->setEpitaph('Lover of life, singer of songs')
            ->setGender(Gender::MALE)
            ->setGravePlace('London, United Kingdom')
            ->setGravePlaceCoordinates('51.5074, -0.1278')
            ->setIsPublished(true)
        ;

        $manager->persist($grave2);
        $this->addReference('grave-freddie', $grave2);

        $manager->flush();
    }

    #[\Override]
    public static function getGroups(): array
    {
        return ['tests'];
    }
}
