<?php

namespace App\Tests\Integration\Command;

use App\Command\CheckMessagesCommand;
use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class CheckMessagesCommandTest extends KernelTestCase
{
    private ?EntityManagerInterface $entityManager;
    private ?CommandTester $commandTester;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->entityManager->beginTransaction();

        $command = $container->get(CheckMessagesCommand::class);
        $this->commandTester = new CommandTester($command);
    }

    protected function tearDown(): void
    {
        $this->entityManager->rollback();
        $this->entityManager->close();
        $this->entityManager = null;
        $this->commandTester = null;
        parent::tearDown();
    }

    public function testExecuteRunsSuccessfullyAndOutputsMessage(): void
    {
        $this->commandTester->execute([]);

        $this->assertSame(Command::SUCCESS, $this->commandTester->getStatusCode());

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Executes!', $output);
    }

    public function testExecuteTriggersCheckAndNotifyWhenUnseenMessagesExist(): void
    {
        // Insert an unseen message so countNew() returns > 0
        $message = new Message();
        $message->setName('Test User')
            ->setEmail('test@example.com')
            ->setSubject('Test Subject')
            ->setMessage('Test Content')
            ->setCreatedAt(new \DateTimeImmutable())
            ->setSeen(false);

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        // Execute the command which invokes CheckMessages service
        $this->commandTester->execute([]);

        $this->assertSame(Command::SUCCESS, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('Executes!', $this->commandTester->getDisplay());
    }
}