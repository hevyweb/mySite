<?php

namespace App\Tests\Integration\Repository;

use App\Entity\Stat;
use App\Repository\StatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class StatRepositoryTest extends KernelTestCase
{
    private ?EntityManagerInterface $entityManager;
    private ?StatRepository $statRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->statRepository = $container->get(StatRepository::class);

        $this->entityManager->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->entityManager->rollback();
        $this->entityManager->close();
        $this->entityManager = null;
        $this->statRepository = null;
        parent::tearDown();
    }

    public function testFindTodayVisitReturnsStatWhenMatchExists(): void
    {
        $path = '/grave/test-path-'.uniqid();
        $visitorHash = 'visitor-hash-123';

        $stat = new Stat();
        $stat->setPath($path)
            ->setVisitorHash($visitorHash)
            ->setCreatedAt(new \DateTimeImmutable('today 14:30:00'));

        $this->entityManager->persist($stat);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $result = $this->statRepository->findTodayVisit($path, $visitorHash);

        $this->assertNotNull($result);
        $this->assertSame($path, $result->getPath());
        $this->assertSame($visitorHash, $result->getVisitorHash());
    }

    public function testFindTodayVisitReturnsNullForOldVisit(): void
    {
        $path = '/grave/old-path-'.uniqid();
        $visitorHash = 'visitor-hash-old';

        $stat = new Stat();
        $stat->setPath($path)
            ->setVisitorHash($visitorHash)
            ->setCreatedAt(new \DateTimeImmutable('yesterday 14:30:00'));

        $this->entityManager->persist($stat);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $result = $this->statRepository->findTodayVisit($path, $visitorHash);

        $this->assertNull($result);
    }

    public function testFindGraveStatisticsReturnsOnlyGravePathStats(): void
    {
        $gravePath = '/grave/item-'.uniqid();
        $otherPath = '/about-us';

        $graveStat = new Stat();
        $graveStat->setPath($gravePath)
            ->setVisitorHash('hash_grave')
            ->setCreatedAt(new \DateTimeImmutable());

        $otherStat = new Stat();
        $otherStat->setPath($otherPath)
            ->setVisitorHash('hash_other')
            ->setCreatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($graveStat);
        $this->entityManager->persist($otherStat);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $results = $this->statRepository->findGraveStatistics();

        $this->assertNotEmpty($results);
        foreach ($results as $result) {
            $this->assertStringStartsWith('/grave/', $result->getPath());
        }
    }
}
