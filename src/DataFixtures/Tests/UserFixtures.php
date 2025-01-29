<?php

namespace App\DataFixtures\Tests;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    const REGULAR_USER = 'user';
    
    const ADMIN_USER = 'admin';
    
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public static function getGroups(): array
    {
        return ['tests'];
    }

    /**
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        $adminUser = $this->createAdminUser();
        $user = $this->createUser();
        $manager->persist($adminUser);
        $manager->persist($user);
        $manager->flush();

        $this->setReference('user_admin', $adminUser);
        $this->setReference('user_user', $user);
    }

    /**
     * @throws Exception
     */
    private function createAdminUser(): User
    {
        $adminUser = new User();
        $adminUser->setBirthDay(new \DateTime('24.02.2022'))
            ->setEmail('admin@fake.com')
            ->setFirstName('John')
            ->setLastName('Smith')
            ->setSex(1)
            ->setUsername(self::ADMIN_USER)
            ->setPlainPassword('admin')
            ->setPassword($this->passwordHasher->hashPassword($adminUser, 'admin'))
            ->setActive(true)
            ->setEnabled(true)
            ->setCreatedAt(new \DateTimeImmutable((new \DateTime('2024-12-31 23:59:59'))->format('c')));

        $adminUser->addRole($this->getReference('role_ROLE_ADMIN'));
        return $adminUser;
    }

    /**
     * @throws Exception
     */
    private function createUser(): User
    {
        $user = new User();
        $user->setBirthDay(new \DateTime('20.02.2014'))
            ->setEmail('user@fake.com')
            ->setFirstName('Jane')
            ->setLastName('Smith')
            ->setSex(0)
            ->setUsername(self::REGULAR_USER)
            ->setPlainPassword('user')
            ->setPassword($this->passwordHasher->hashPassword($user, 'user'))
            ->setActive(true)
            ->setEnabled(true)
            ->setCreatedAt(new \DateTimeImmutable((new \DateTime('2025-01-01 00:00:00'))->format('c')));

        $user->addRole($this->getReference('role_ROLE_USER'));
        return $user;
    }

    public function getDependencies(): array
    {
        return [
            RoleFixtures::class,
        ];
    }
}
