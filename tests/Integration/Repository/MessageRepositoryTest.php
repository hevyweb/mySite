<?php

namespace App\Tests\Integration\Repository;

use App\Entity\Message;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MessageRepositoryTest extends KernelTestCase
{
    private ?EntityManagerInterface $entityManager;
    private ?MessageRepository $messageRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->messageRepository = $container->get(MessageRepository::class);

        $this->entityManager->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->entityManager->rollback();
        $this->entityManager->close();
        $this->entityManager = null;
        $this->messageRepository = null;
        parent::tearDown();
    }

    public function testCountMessagesReturnsTotalCount(): void
    {
        $message1 = new Message();
        $message1->setName('Test User 1')
            ->setEmail('test1@example.com')
            ->setSubject('Subject 1')
            ->setMessage('Message body content 1')
            ->setCreatedAt(new \DateTimeImmutable())
            ->setSeen(false);

        $message2 = new Message();
        $message2->setName('Test User 2')
            ->setEmail('test2@example.com')
            ->setSubject('Subject 2')
            ->setMessage('Message body content 2')
            ->setCreatedAt(new \DateTimeImmutable())
            ->setSeen(true);

        $this->entityManager->persist($message1);
        $this->entityManager->persist($message2);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $count = $this->messageRepository->countMessages();

        $this->assertGreaterThanOrEqual(2, $count);
    }

    public function testCountNewReturnsOnlyUnseenMessagesCount(): void
    {
        $unseenMessage = new Message();
        $unseenMessage->setName('Unseen User')
            ->setEmail('unseen@example.com')
            ->setSubject('New Subject')
            ->setMessage('New content')
            ->setCreatedAt(new \DateTimeImmutable())
            ->setSeen(false);

        $seenMessage = new Message();
        $seenMessage->setName('Seen User')
            ->setEmail('seen@example.com')
            ->setSubject('Old Subject')
            ->setMessage('Old content')
            ->setCreatedAt(new \DateTimeImmutable())
            ->setSeen(true);

        $this->entityManager->persist($unseenMessage);
        $this->entityManager->persist($seenMessage);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $newCount = $this->messageRepository->countNew();

        $this->assertGreaterThanOrEqual(1, $newCount);
    }
}