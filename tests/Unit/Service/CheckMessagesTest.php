<?php

namespace App\Tests\Unit\Service;

use App\Entity\Message;
use App\Event\NewMessagesEvent;
use App\Repository\MessageRepository;
use App\Service\CheckMessages;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CheckMessagesTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private EventDispatcherInterface&MockObject $dispatcher;
    private CheckMessages $checkMessages;
    private MessageRepository&MockObject $messageRepository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->messageRepository = $this->createMock(MessageRepository::class);

        $this->entityManager->method('getRepository')
            ->with(Message::class)
            ->willReturn($this->messageRepository);

        $this->checkMessages = new CheckMessages($this->entityManager, $this->dispatcher);
    }

    public function testCheckAndNotifyDispatchesEventWhenNewMessagesExist(): void
    {
        $this->messageRepository->expects($this->once())
            ->method('countNew')
            ->willReturn(3);

        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function ($event) {
                return $event instanceof NewMessagesEvent && 3 === $event->getNewMessages();
            }));

        $this->checkMessages->checkAndNotify();
    }

    public function testCheckAndNotifyDoesNotDispatchEventWhenNoNewMessages(): void
    {
        $this->messageRepository->expects($this->once())
            ->method('countNew')
            ->willReturn(0);

        $this->dispatcher->expects($this->never())
            ->method('dispatch');

        $this->checkMessages->checkAndNotify();
    }
}
