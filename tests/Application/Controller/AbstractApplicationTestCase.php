<?php

namespace App\Tests\Application\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class AbstractApplicationTestCase extends WebTestCase
{
    const DEFAULT_ADMIN_ID = 42;

    protected KernelBrowser $client;

    protected RouterInterface $rout;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->loginUser($this->getAdminUser());
        $this->router = $this->getContainer()->get(RouterInterface::class);
    }

    private function getAdminUser(): UserInterface
    {
        /**
         * @var EntityManagerInterface $em
         */
        $em = $this->getContainer()->get(EntityManagerInterface::class);

        $repository = $em->getRepository(User::class);

        $user = $repository->find(static::DEFAULT_ADMIN_ID);

        if (!$user) {
            throw new \RuntimeException('Users has not been loaded before running tests.');
        }

        return $user;
    }
}