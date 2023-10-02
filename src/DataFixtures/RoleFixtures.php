<?php

namespace App\DataFixtures;

use App\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;

class RoleFixtures extends Fixture
{
    use LoadPredefinedDataTrait;

    public function load(ObjectManager $manager): void
    {
        $faker = FakerFactory::create();

        $this->loadPredefinedRoles($manager);
        $roles = ['admin', 'user'];

        for ($n = 0; $n < 10; $n++) {
            $role = new Role();
            do {
                $roleName = $faker->word();
            } while (in_array($roleName, $roles));
            $roles[] = $roleName;
            $role->setCode('ROLE_' . strtoupper($roleName))
                ->setLabel($roleName);
            $manager->persist($role);
            $this->setReference('role_' . $n, $role);
        }

        $manager->flush();
    }

    private function loadPredefinedRoles(ObjectManager $manager): void
    {
        $roles = $this->loadCSV(Role::class, __DIR__ . '/data/db_role.csv');
        foreach ($roles as $role) {
            $manager->persist($role);
            $this->setReference('role_' . $role->getCode(), $role);
        }

        $manager->flush();
    }
}
