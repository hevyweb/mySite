<?php

namespace App\Tests\Application\Controller;

use App\DataFixtures\Tests\UserFixtures;
use App\Entity\User;
use App\Exception\UserNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;

abstract class AbstractApplicationTestCase extends WebTestCase
{
    public const DEFAULT_ADMIN_ID = 42;

    protected KernelBrowser $client;

    protected RouterInterface $router;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->router = $this->getContainer()->get(RouterInterface::class);
    }

    protected function logInUser(?string $userName = UserFixtures::REGULAR_USER): User
    {
        $user = $this->getUser($userName);
        $this->client->loginUser($user);

        return $user;
    }

    protected function logInAdmin(?string $adminUserName = UserFixtures::ADMIN_USER): User
    {
        return $this->logInUser($adminUserName);
    }

    protected function getUser(string $userName): User
    {
        /**
         * @var EntityManagerInterface $em
         */
        $em = $this->getContainer()->get(EntityManagerInterface::class);

        $repository = $em->getRepository(User::class);

        $user = $repository->findOneByUsername($userName);

        if (!$user) {
            throw new UserNotFoundException('Users has not been loaded before running tests.');
        }

        return $user;
    }
}
