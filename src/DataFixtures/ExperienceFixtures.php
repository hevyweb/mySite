<?php

namespace App\DataFixtures;

use App\Entity\Experience;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;
use Faker\Generator;

class ExperienceFixtures extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = FakerFactory::create();
        $fakerUA = FakerFactory::create('uk_UA');

        $this->buildTenEntries($faker, $manager, 'en');
        $this->buildTenEntries($fakerUA, $manager, 'ua');
    }

    public function buildTenEntries(Generator $faker, ObjectManager $manager, string $locale): void
    {
        for ($n = 0; $n < 10; ++$n) {
            $experience = new Experience();
            $experience->setLocale($locale)
                ->setCompany($faker->company())
                ->setDescription($faker->text())
                ->setName($faker->title())
                ->setFromDate($faker->dateTimeBetween('-15 years'))
                ->setToDate($faker->dateTimeBetween($experience->getFromDate()))
                ->setLocation($faker->city)
                ->setImage('no-logo.png')
            ;
            $manager->persist($experience);
        }

        $manager->flush();
        $manager->clear();
        gc_collect_cycles();
    }

    public static function getGroups(): array
    {
        return ['default'];
    }
}
