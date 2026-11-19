<?php

namespace App\DataFixtures\Tests;

use App\Entity\Condolence;
use App\Entity\Grave;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CondolenceFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $grave = $this->getReference('grave', Grave::class);

        $condolence = new Condolence();
        $condolence->setName('John Doe');
        $condolence->setDescription('Rest in peace. A true legend.');
        $condolence->setIsActive(true);
        $condolence->setCreationDate(new \DateTimeImmutable());
        $condolence->setGrave($grave);

        $manager->persist($condolence);
        $manager->flush();

        $this->addReference('condolence', $condolence);
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
