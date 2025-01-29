<?php

namespace App\DataFixtures;

use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;

class TagFixture extends Fixture implements FixtureGroupInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = FakerFactory::create();
        for ($n = 0; $n < 100; ++$n) {
            $tag = new Tag();
            $tag->setName(substr($faker->unique()->slug(2), 0, 60));
            $this->entityManager->persist($tag);
            $this->setReference('tag_'.$n, $tag);
        }

        $this->entityManager->flush();
        $manager->clear();
    }

    public static function getGroups(): array
    {
        return ['default'];
    }
}
