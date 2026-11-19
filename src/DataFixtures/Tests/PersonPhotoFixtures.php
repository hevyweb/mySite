<?php

namespace App\DataFixtures\Tests;

use App\Entity\Grave;
use App\Entity\PersonPhoto;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PersonPhotoFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    /**
     * @throws \Exception
     */
    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $grave = $this->getReference('grave', Grave::class);

        $photo = new PersonPhoto();
        $photo
            ->setFilename('test_test.jpg')
            ->setDescription('Test photo description for Michael Jackson')
            ->setShootingDate(\DateTime::createFromFormat('d.m.Y', '15.07.1988'))
            ->setGrave($grave)
        ;

        $manager->persist($photo);
        $manager->flush();
    }

    #[\Override]
    public function getDependencies(): array
    {
        return [
            GraveFixtures::class,
        ];
    }

    #[\Override]
    public static function getGroups(): array
    {
        return ['tests'];
    }
}
