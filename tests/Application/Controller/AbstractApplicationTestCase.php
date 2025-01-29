<?php

namespace App\Tests\Application\Controller;

use App\DataFixtures\Tests\UserFixtures;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class AbstractApplicationTestCase extends WebTestCase
{
    public const DEFAULT_ADMIN_ID = 42;

    protected KernelBrowser $client;

    protected RouterInterface $rout;
    
    protected function logInUser(?string $userName = UserFixtures::REGULAR_USER): void
    {
        $user = $this->getUser($userName);
        $this->client->loginUser($user);
    }
    
    protected function logInAdmin(?string $adminUserName = UserFixtures::ADMIN_USER): void
    {
        $this->logInUser($adminUserName);
    }

    protected function getUser(string $userName): UserInterface
    {
        /**
         * @var EntityManagerInterface $em
         */
        $em = $this->getContainer()->get(EntityManagerInterface::class);

        $repository = $em->getRepository(User::class);

        $user = $repository->findOneByUsername($userName);

        if (!$user) {
            throw new \RuntimeException('Users has not been loaded before running tests.');
        }

        return $user;
    }
}
