<?php

namespace App\Tests\Integration\Command;

use App\Command\GenerateUserCommand;
use App\Entity\Role;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class GenerateUserCommandTest extends KernelTestCase
{
    private ?EntityManagerInterface $entityManager;
    private ?CommandTester $commandTester;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->entityManager->beginTransaction();

        $command = $container->get(GenerateUserCommand::class);
        $this->commandTester = new CommandTester($command);
    }

    protected function tearDown(): void
    {
        $this->entityManager->rollback();
        $this->entityManager->close();
        $this->entityManager = null;
        $this->commandTester = null;
        parent::tearDown();
    }

    public function testExecuteCreatesUserSuccessfully(): void
    {
        $uniqueUsername = 'generated-admin-' . uniqid();

        $this->commandTester->execute([
            '--username' => $uniqueUsername,
            '--password' => 'custom-password-123',
        ]);

        $this->assertSame(Command::SUCCESS, $this->commandTester->getStatusCode());

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('New admin user created', $output);
        $this->assertStringContainsString($uniqueUsername, $output);

        // Verify the user is successfully persisted in the database with roles and encoded password
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $uniqueUsername]);
        $this->assertNotNull($user);
        $this->assertTrue($user->getEnabled());
        $this->assertTrue($user->getActive());
        $this->assertNotEmpty($user->getRoles());
    }

    public function testExecuteFailsWhenUserAlreadyExists(): void
    {
        $existingUsername = 'existing-user-' . uniqid();

        // Pre-insert a user with the same username
        $existingUser = new User();
        $existingUser->setUsername($existingUsername)
            ->setEmail('existing-' . uniqid() . '@fake.com')
            ->setFirstName('Test')
            ->setLastName('User')
            ->setPassword('dummyhash')
            ->setEnabled(true)
            ->setActive(true)
            ->setCreatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($existingUser);
        $this->entityManager->flush();

        // Execute command using the already taken username
        $this->commandTester->execute([
            '--username' => $existingUsername,
        ]);

        $this->assertSame(1, $this->commandTester->getStatusCode());

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('already exists', $output);
    }

    public function testExecuteThrowsRuntimeExceptionWhenRolesAreMissing(): void
    {
        // Remove existing roles to simulate unmigrated/missing roles in the database
        $roleRepository = $this->entityManager->getRepository(Role::class);
        $adminRole = $roleRepository->findOneBy(['code' => Role::ROLE_ADMIN]);
        $userRole = $roleRepository->findOneBy(['code' => 'ROLE_USER']);

        if ($adminRole) {
            $this->entityManager->remove($adminRole);
        }
        if ($userRole) {
            $this->entityManager->remove($userRole);
        }
        $this->entityManager->flush();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Run migrations first. Roles have not been set yet');

        $this->commandTester->execute([
            '--username' => 'any-username',
        ]);
    }
}