<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class NewMessagesEvent extends Event
{
    public function __construct(private readonly int $newMessages)
    {
    }

    public function getNewMessages(): int
    {
        return $this->newMessages;
    }
}
