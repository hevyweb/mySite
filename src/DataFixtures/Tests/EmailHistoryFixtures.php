<?php

namespace App\DataFixtures\Tests;

use App\Entity\EmailHistory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class EmailHistoryFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public static function getGroups(): array
    {
        return ['tests'];
    }

    public function load(ObjectManager $manager): void
    {
        $token = str_repeat('aA-zA123', 8);
        $emailHistory = new EmailHistory();
        $emailHistory
            ->setUser($this->getReference('test_user'))
            ->setCreatedAt(new \DateTimeImmutable())
            ->setNewEmail('test2@fake.com')
            ->setOldEmail('user@fake.com')
            ->setNewConfirmationToken($token)
            ->setOldConfirmationToken($token)
            ->setCompleted(false)
        ;
        
        $manager->persist($emailHistory);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}