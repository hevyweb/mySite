<?php

namespace App\DataFixtures\Tests;

use App\Entity\EmailHistory;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class EmailHistoryFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    #[\Override]
    public static function getGroups(): array
    {
        return ['tests'];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $token = str_repeat('aA-zA123', 8);
        $emailHistory = new EmailHistory();
        $emailHistory
            ->setUser($this->getReference('test_user', User::class))
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

    #[\Override]
    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
