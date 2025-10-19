<?php

namespace App\DataFixtures\Tests;

use App\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class RoleFixtures extends Fixture implements FixtureGroupInterface
{
    #[\Override]
    public static function getGroups(): array
    {
        return ['tests'];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        // user role
        $userRole = new Role();
        $userRole->setLabel('User')
            ->setCode('ROLE_USER');
        $manager->persist($userRole);

        // admin role
        $adminRole = new Role();
        $adminRole->setLabel('Admin')
            ->setCode('ROLE_ADMIN');
        $manager->persist($adminRole);
        $manager->flush();

        $this->setReference('role_'.$userRole->getCode(), $userRole);
        $this->setReference('role_'.$adminRole->getCode(), $adminRole);
    }
}
