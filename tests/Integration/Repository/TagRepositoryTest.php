<?php

namespace App\Tests\Integration\Repository;

use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TagRepositoryTest extends KernelTestCase
{
    private ?EntityManagerInterface $entityManager;
    private ?TagRepository $tagRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->tagRepository = $container->get(TagRepository::class);

        $this->entityManager->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->entityManager->rollback();
        $this->entityManager->close();
        $this->entityManager = null;
        $this->tagRepository = null;
        parent::tearDown();
    }

    public function testSearchReturnsMatchingTagsFromPreloadedFixtures(): void
    {
        // 'Apple' is pre-loaded via TagFixtures
        $results = $this->tagRepository->search('App');

        $this->assertNotEmpty($results);
        $this->assertSame('Apple', $results[0]->getName());
    }

    public function testSearchReturnsEmptyArrayWhenNoMatchFound(): void
    {
        $results = $this->tagRepository->search('NonExistentFruit');

        $this->assertEmpty($results);
    }
}