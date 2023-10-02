<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = FakerFactory::create();
        $fakerUA = FakerFactory::create('uk_UA');

        $this->createAdminUser($manager, $faker);

        for($n = 0; $n < 10; $n++) {
            $user = new User();
            $user->setBirthDay($faker->dateTimeBetween('-60 years', '-18 years'))
                ->setEmail($faker->email)
                ->setFirstName($fakerUA->firstName())
                ->setLastName($fakerUA->lastName())
                ->setSex($faker->numberBetween(0, 1))
                ->setUsername($faker->userName)
                ->setPlainPassword('admin')
                ->setPassword($this->passwordHasher->hashPassword($user, 'admin'))
                ->setActive($faker->boolean(90))
                ->setEnabled($faker->boolean(90))
                ->addRole($this->getReference('role_' . $n))
                ->setCreatedAt(new \DateTimeImmutable($faker->dateTimeBetween()->format('c')))
            ;
            $manager->persist($user);
        }

        $manager->flush();
    }

    private function createAdminUser(ObjectManager $manager, Generator $faker): void
    {
        $user = new User();
        $user->setBirthDay($faker->dateTimeBetween('-60 years', '-18 years'))
            ->setEmail($faker->email)
            ->setFirstName($faker->firstName())
            ->setLastName($faker->lastName())
            ->setSex($faker->numberBetween(0, 1))
            ->setUsername('admin')
            ->setPlainPassword('admin')
            ->setPassword($this->passwordHasher->hashPassword($user, 'admin'))
            ->setActive(true)
            ->setEnabled(true)
            ->setCreatedAt(new \DateTimeImmutable($faker->dateTimeBetween()->format('c')));

        $user->addRole($this->getReference('role_ROLE_ADMIN'));

        $this->setReference('user_admin', $user);
        $manager->persist($user);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            RoleFixtures::class,
        ];
    }
}
