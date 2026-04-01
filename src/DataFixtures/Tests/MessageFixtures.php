<?php

namespace App\DataFixtures\Tests;

use App\Entity\Message;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class MessageFixtures extends Fixture implements FixtureGroupInterface
{
    #[\Override]
    public static function getGroups(): array
    {
        return ['tests'];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $message = new Message();
            $message->setName('Test User ' . $i)
                ->setEmail('test' . $i . '@example.com')
                ->setSubject('Subject ' . $i)
                ->setMessage('Message body content ' . $i)
                ->setCreatedAt(new \DateTimeImmutable())
                ->setSeen(false);
            $manager->persist($message);
            
            if ($i === 1) {
                $this->setReference('message_test', $message);
            }
        }

        $manager->flush();
    }
}
