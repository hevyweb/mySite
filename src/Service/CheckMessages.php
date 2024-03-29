<?php

namespace App\Service;

use App\Entity\Message;
use App\Event\NewMessagesEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

readonly class CheckMessages
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EventDispatcherInterface $dispatcher,
    ) {
    }

    public function checkAndNotify(): void
    {
        $newMessages = $this->entityManager->getRepository(Message::class)->countNew();
        if ($newMessages) {
            $this->dispatcher->dispatch(new NewMessagesEvent($newMessages));
        }
    }
}
