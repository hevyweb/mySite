<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as AbstractSymfonyController;

abstract class AbstractController extends AbstractSymfonyController
{
    public function getUserEntity(): User
    {
        $user = $this->getUser();
        
        if (!($user instanceof User)) {
            throw new \LogicException('The user must be an instance of User.');
        }
        return $user;
    }
}