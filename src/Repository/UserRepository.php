<?php

namespace App\Repository;

use App\DTO\UserSearch;
use App\Entity\Role;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @template-extends EntityRepository<User>
 *
 * @method User|null find(int|string $id, ?int $lockMode = null, ?int $lockVersion = null)
 * @method User|null findOneBy(array<string, mixed> $criteria, array<string, 'ASC'|'DESC'>|null $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array<string, mixed> $criteria, array<string, 'ASC'|'DESC'>|null $orderBy = null, ?int $limit = null, ?int $offset = null)
 * @method User|null findOneByUsername(string $username)
 */
class UserRepository extends EntityRepository implements UserLoaderInterface
{
    #[\Override]
    public function loadUserByIdentifier(string $identifier): ?UserInterface
    {
        return $this->find($identifier);
    }

    /**
     * @return User[]
     */
    public function getAdmins(): array
    {
        return $this->createQueryBuilder('u')
            ->innerJoin(Role::class, 'r')
            ->where('r.code = :admin_role')
            ->setParameter('admin_role', Role::ROLE_ADMIN)
            ->getQuery()
            ->getResult();
    }

    public function total(UserSearch $userSearch): int
    {
        return (int) $this->createBasicSearchQuery($userSearch)
            ->select('COUNT(u)')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return User[]
     */
    public function search(UserSearch $userSearch): array
    {
        return $this->createBasicSearchQuery($userSearch)
            ->orderBy('u.'.$userSearch->sorting, $userSearch->dir)
            ->setMaxResults($userSearch->limit)
            ->setFirstResult(($userSearch->page - 1) * $userSearch->limit)
            ->getQuery()
            ->getResult();
    }

    protected function createBasicSearchQuery(UserSearch $userSearch): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('u');

        if (!empty($userSearch->search)) {
            $queryBuilder
                ->where('u.username LIKE :username')
                ->setParameter('username', '%'.$userSearch->search.'%');
        }

        return $queryBuilder;
    }
}
