<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const NUM_USERS = 10;

    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    /**
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        $faker = FakerFactory::create();
        $fakerUA = FakerFactory::create('uk_UA');
        for ($n = 0; $n < self::NUM_USERS; ++$n) {
            try {
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
                    ->addRole($this->getReference('role_'.$n))
                    ->setCreatedAt(new \DateTimeImmutable($faker->dateTimeBetween()->format('c')));
                $manager->persist($user);
                $manager->flush();
                $manager->clear();
            } catch (UniqueConstraintViolationException $exception) {
                // this exception means faker generated not unique username or email.
                --$n;
                continue;
            }

            $this->setReference('user_'.$n, $user);
        }
    }

    public function getDependencies(): array
    {
        return [
            RoleFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['default'];
    }
}
