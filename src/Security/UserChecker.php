<?php

namespace App\Security;

use App\Entity\User;
use App\Exception\UserBannedException;
use App\Exception\UserNotActiveException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    #[\Override]
    public function checkPreAuth(UserInterface|User $user): void
    {
        if (!$user instanceof User) {
            throw new \Exception('Wrong user instance provided.');
        }

        if (!$user->getActive()) {
            throw new UserNotActiveException('Your user account is not active. Please confirm your email.');
        }

        if (!$user->getEnabled()) {
            throw new UserBannedException('User account is blocked due to site rules violation.');
        }
    }

    #[\Override]
    public function checkPostAuth(UserInterface $user): void
    {
    }
}
