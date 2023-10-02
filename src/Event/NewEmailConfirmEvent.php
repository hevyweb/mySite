<?php

namespace App\Event;

use App\Entity\EmailHistory;
use Symfony\Contracts\EventDispatcher\Event;

class NewEmailConfirmEvent extends Event
{
    private EmailHistory $emailHistory;

    public function __construct(EmailHistory $emailHistory)
    {
        $this->emailHistory = $emailHistory;
    }

    public function getEmailHistory(): EmailHistory
    {
        return $this->emailHistory;
    }
}