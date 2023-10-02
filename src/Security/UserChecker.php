<?php

namespace App\Security;

use App\Exception\UserBannedException;
use App\Exception\UserNotActiveException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user->getActive()) {
            throw new UserNotActiveException('Your user account is not active. Please confirm your email.');
        }

        if (!$user->getEnabled()) {
            throw new UserBannedException('User account is blocked due to site rules violation.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}
