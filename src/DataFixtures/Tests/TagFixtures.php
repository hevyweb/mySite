<?php

namespace App\DataFixtures\Tests;

use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class TagFixtures extends Fixture implements FixtureGroupInterface
{
    #[\Override]
    public static function getGroups(): array
    {
        return ['tests'];
    }

    #[\Override]
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

        $this->addReference('tag_apple', $tagApple);
        $this->addReference('tag_banana', $tagBanana);
        $this->addReference('tag_plum', $tagPlum);
    }
}
