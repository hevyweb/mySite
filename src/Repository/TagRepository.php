<?php

namespace App\Repository;

use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tag>
 *
 * @method Tag|null find(int|string $id, ?int $lockMode = null, ?int $lockVersion = null)
 * @method Tag|null findOneBy(array<string, mixed> $criteria, array<string, 'ASC'|'DESC'>|null $orderBy = null)
 * @method Tag[]    findAll()
 * @method Tag[]    findBy(array<string, mixed> $criteria, array<string, 'ASC'|'DESC'>|null $orderBy = null, ?int $limit = null, ?int $offset = null)
 */
class TagRepository extends ServiceEntityRepository
{
    /**
     * @psalm-suppress PossiblyUnusedParam
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    /**
     * @psalm-suppress PossiblyUnusedParam
     *
     * @return Tag[]
     */
    public function search(string $name): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.name LIKE :name')
            ->setParameter('name', '%'.$name.'%')
            ->getQuery()
            ->getResult();
    }
}
