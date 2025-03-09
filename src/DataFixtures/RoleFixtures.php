<?php

namespace App\DataFixtures;

use App\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;

class RoleFixtures extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = FakerFactory::create();

        $roles = ['admin', 'user'];

        for ($n = 0; $n < 10; ++$n) {
            $role = new Role();
            do {
                $roleName = $faker->word();
            } while (in_array($roleName, $roles));
            $roles[] = $roleName;
            $role->setCode('ROLE_'.strtoupper($roleName))
                ->setLabel($roleName);
            $manager->persist($role);
            $this->setReference('role_'.$n, $role);
        }

        $manager->flush();
        $manager->clear();
    }

    public static function getGroups(): array
    {
        return ['default'];
    }
}
