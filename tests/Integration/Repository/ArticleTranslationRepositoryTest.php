<?php

namespace App\Tests\Integration\Repository;

use App\DataFixtures\Tests\UserFixtures;
use App\Entity\Article;
use App\Entity\ArticleTranslation;
use App\Entity\User;
use App\Repository\ArticleTranslationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ArticleTranslationRepositoryTest extends KernelTestCase
{
    private ?EntityManagerInterface $entityManager;
    private ?ArticleTranslationRepository $articleTranslationRepository;
    private ?User $testUser;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->articleTranslationRepository = $container->get(ArticleTranslationRepository::class);

        $this->entityManager->beginTransaction();

        $this->testUser = $this->entityManager->getRepository(User::class)->findOneBy(['username' => UserFixtures::ADMIN_USER]);
    }

    protected function tearDown(): void
    {
        $this->entityManager->rollback();
        $this->entityManager->close();
        $this->entityManager = null;
        $this->articleTranslationRepository = null;
        $this->testUser = null;
        parent::tearDown();
    }

    public function testGetTranslationBySlugReturnsTranslationForValidSlugAndLocale(): void
    {
        $uniqueSlug = 'unique-article-slug-' . uniqid();
        $locale = 'en';

        $article = new Article();
        $article->setSlug($uniqueSlug);

        $translation = (new ArticleTranslation())
            ->setLocale($locale)
            ->setTitle('Test Translation Title')
            ->setBody('Test translation body')
            ->setDraft(false)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTime())
            ->setCreatedBy($this->testUser)
            ->setUpdatedBy($this->testUser)
            ->setArticle($article);

        $article->addArticleTranslation($translation);

        $this->entityManager->persist($article);
        $this->entityManager->persist($translation);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $result = $this->articleTranslationRepository->getTranslationBySlug($uniqueSlug, $locale);

        $this->assertInstanceOf(ArticleTranslation::class, $result);
        $this->assertSame('Test Translation Title', $result->getTitle());
        $this->assertSame($uniqueSlug, $result->getArticle()->getSlug());
    }

    public function testGetTranslationBySlugThrowsNoResultExceptionWhenNotFound(): void
    {
        $this->expectException(NoResultException::class);

        $this->articleTranslationRepository->getTranslationBySlug('non-existent-slug', 'en');
    }
}