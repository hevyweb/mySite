<?php

namespace App\DataFixtures\Tests;

use App\Entity\Stat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class StatFixtures extends Fixture implements FixtureGroupInterface
{
    #[\Override]
    public static function getGroups(): array
    {
        return ['tests'];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        // Today's visit matching grave path
        $stat1 = new Stat();
        $stat1->setPath('/grave/123')
            ->setVisitorHash('hash_today_1')
            ->setCreatedAt(new \DateTimeImmutable('today 10:00:00'));
        $manager->persist($stat1);

        // Today's visit with non-grave path
        $stat2 = new Stat();
        $stat2->setPath('/home')
            ->setVisitorHash('hash_today_2')
            ->setCreatedAt(new \DateTimeImmutable('today 12:00:00'));
        $manager->persist($stat2);

        // Old visit with grave path (yesterday)
        $stat3 = new Stat();
        $stat3->setPath('/grave/456')
            ->setVisitorHash('hash_old')
            ->setCreatedAt(new \DateTimeImmutable('yesterday 15:00:00'));
        $manager->persist($stat3);

        $manager->flush();
    }
}