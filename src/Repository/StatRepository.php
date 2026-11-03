<?php

namespace App\Repository;

use App\Entity\Stat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Stat>
 */
class StatRepository extends ServiceEntityRepository
{
    /**
     * @psalm-suppress PossiblyUnusedParam
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stat::class);
    }

    /**
     * @psalm-suppress PossiblyUnusedParam
     */
    public function findTodayVisit(string $path, string $visitorHash): ?Stat
    {
        $startOfDay = new \DateTimeImmutable('today 00:00:00');
        $endOfDay = new \DateTimeImmutable('today 23:59:59');

        return $this->createQueryBuilder('s')
            ->andWhere('s.path = :path')
            ->andWhere('s.visitorHash = :visitorHash')
            ->andWhere('s.createdAt BETWEEN :start AND :end')
            ->setParameter('path', $path)
            ->setParameter('visitorHash', $visitorHash)
            ->setParameter('start', $startOfDay)
            ->setParameter('end', $endOfDay)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return array<Stat>
     */
    public function findGraveStatistics(): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.path LIKE :gravePath')
            ->setParameter('gravePath', '/grave/%')
            ->getQuery()
            ->getResult();
    }
}
