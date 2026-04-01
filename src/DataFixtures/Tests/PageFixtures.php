<?php

namespace App\DataFixtures\Tests;

use App\Entity\Page;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PageFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    #[\Override]
    public static function getGroups(): array
    {
        return ['tests'];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $page = new Page();
        $page->setTitle('Initial Title')
            ->setBody('Initial Body')
            ->setLocale('en')
            ->setRoute('home')
            ->setCreatedAt(new \DateTimeImmutable())
            ->setCreatedBy($this->getReference('test_admin', User::class));

        $manager->persist($page);
        $manager->flush();

        $this->addReference('page_test', $page);
    }

    #[\Override]
    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
