<?php

namespace App\Event;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class RecoverPasswordEvent extends AbstractUserEvent
{
}