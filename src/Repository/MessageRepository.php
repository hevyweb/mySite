<?php

namespace App\Repository;

use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Message>
 *
 * @method Message|null find(int|string $id, ?int $lockMode = null, ?int $lockVersion = null)
 * @method Message|null findOneBy(array<string, mixed> $criteria, array<string, 'ASC'|'DESC'>|null $orderBy = null)
 * @method Message[]    findAll()
 * @method Message[]    findBy(array<string, mixed> $criteria, array<string, 'ASC'|'DESC'>|null $orderBy = null, ?int $limit = null, ?int $offset = null)
 *
 * @seal-methods
 */
class MessageRepository extends ServiceEntityRepository
{
    /**
     * @psalm-suppress PossiblyUnusedParam
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    public function countNew(): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('count(m) as c')
            ->where('m.seen = 0')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countMessages(): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('count(m) as c')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
