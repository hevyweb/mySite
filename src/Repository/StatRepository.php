<?php

namespace App\Repository;

use App\Entity\Stat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class StatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stat::class);
    }

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
    
    public function findGraveStatistics(): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.path LIKE :gravePath')
            ->setParameter('gravePath', '/grave/%')
            ->getQuery()
            ->getResult();
    }
}