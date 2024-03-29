<?php

namespace App\Event;

use App\Entity\User;

class ResetPasswordEvent extends AbstractUserEvent
{
    private string $password;

    public function __construct(User $user, string $password)
    {
        parent::__construct($user);
        $this->password = $password;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
