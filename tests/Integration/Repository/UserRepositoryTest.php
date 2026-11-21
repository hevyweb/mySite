<?php

namespace App\Tests\Integration\Repository;

use App\DataFixtures\Tests\UserFixtures;
use App\DTO\UserSearch;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{
    private ?EntityManagerInterface $entityManager;
    private ?UserRepository $userRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->userRepository = $this->entityManager->getRepository(User::class);

        $this->entityManager->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->entityManager->rollback();
        $this->entityManager->close();
        $this->entityManager = null;
        $this->userRepository = null;
        parent::tearDown();
    }

    public function testGetAdminsReturnsAdminUsers(): void
    {
        $admins = $this->userRepository->getAdmins();

        $this->assertNotEmpty($admins);
        $foundAdmin = false;
        foreach ($admins as $admin) {
            if (UserFixtures::ADMIN_USER === $admin->getUsername()) {
                $foundAdmin = true;
                break;
            }
        }
        $this->assertTrue($foundAdmin);
    }

    public function testLoadUserByIdentifierReturnsUser(): void
    {
        $admin = $this->userRepository->findOneBy(['username' => UserFixtures::ADMIN_USER]);
        /** @var User $loadedUser */
        $loadedUser = $this->userRepository->loadUserByIdentifier((string) $admin->getId());

        $this->assertSame($admin->getId(), $loadedUser->getId());
    }

    public function testSearchAndTotalWithUserSearch(): void
    {
        $userSearch = new UserSearch(
            sorting: 'id',
            dir: Criteria::ASC,
            page: 1,
            search: UserFixtures::ADMIN_USER,
            limit: 10,
        );

        $total = $this->userRepository->total($userSearch);
        $results = $this->userRepository->search($userSearch);

        $this->assertSame(1, $total);
        $this->assertCount(1, $results);
        $this->assertSame(UserFixtures::ADMIN_USER, $results[0]->getUsername());
    }
}
