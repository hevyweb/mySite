<?php

namespace App\DataFixtures\Tests;

use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class TagFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['tests'];
    }

    public function load(ObjectManager $manager): void
    {
        $tagApple = new Tag();
        $tagApple->setName('Apple');
        $tagBanana = new Tag();
        $tagBanana->setName('Banana');
        $tagPlum = new Tag();
        $tagPlum->setName('Plum');
        
        $manager->persist($tagApple);
        $manager->persist($tagBanana);
        $manager->persist($tagPlum);

        $manager->flush();
    }
}