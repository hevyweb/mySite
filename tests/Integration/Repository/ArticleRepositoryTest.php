<?php

namespace App\Tests\Integration\Repository;

use App\DataFixtures\Tests\UserFixtures;
use App\DTO\SearchArticle;
use App\Entity\Article;
use App\Entity\ArticleTranslation;
use App\Entity\Tag;
use App\Entity\User;
use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ArticleRepositoryTest extends KernelTestCase
{
    private ?EntityManagerInterface $entityManager;
    private ?ArticleRepository $articleRepository;
    private ?User $testUser;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->articleRepository = $container->get(ArticleRepository::class);

        $this->entityManager->beginTransaction();

        $this->testUser = $this->entityManager->getRepository(User::class)->findOneBy(['username' => UserFixtures::ADMIN_USER]);
    }

    protected function tearDown(): void
    {
        $this->entityManager->rollback();
        $this->entityManager->close();
        $this->entityManager = null;
        $this->articleRepository = null;
        $this->testUser = null;
        parent::tearDown();
    }

    public function testGetBlogArticlesReturnsPublishedArticlesForLocale(): void
    {
        $uniqueSlug = 'test-blog-article-'.uniqid();
        $article = new Article();
        $article->setSlug($uniqueSlug);

        $translation = (new ArticleTranslation())
            ->setLocale('en')
            ->setTitle('Symfony Unit Testing Unique')
            ->setBody('Content body')
            ->setDraft(false)
            ->setHit(100)
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

        $articles = $this->articleRepository->getBlogArticles(100, 1, 'en');

        $filtered = array_filter($articles, fn (Article $a) => $a->getSlug() === $uniqueSlug);

        $this->assertCount(1, $filtered);
        $foundArticle = array_values($filtered)[0];
        $this->assertSame('Symfony Unit Testing Unique', $foundArticle->getArticleTranslations()->first()->getTitle());
    }

    public function testGetBlogArticlesExcludesDraftsAndWrongLocales(): void
    {
        $draftSlug = 'draft-article-'.uniqid();
        $draftArticle = new Article();
        $draftArticle->setSlug($draftSlug);
        $draftTranslation = (new ArticleTranslation())
            ->setLocale('en')
            ->setTitle('Draft Title')
            ->setBody('Draft body')
            ->setDraft(true)
            ->setHit(10)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTime())
            ->setCreatedBy($this->testUser)
            ->setUpdatedBy($this->testUser)
            ->setArticle($draftArticle);

        $draftArticle->addArticleTranslation($draftTranslation);

        $this->entityManager->persist($draftArticle);
        $this->entityManager->persist($draftTranslation);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $articles = $this->articleRepository->getBlogArticles(100, 1, 'en');
        $filtered = array_filter($articles, fn (Article $a) => $a->getSlug() === $draftSlug);

        $this->assertCount(0, $filtered);
    }

    public function testGetTopArticlesOrdersByHitCount(): void
    {
        $uniqueSlug = 'top-hits-'.uniqid();
        $highHitArticle = new Article();
        $highHitArticle->setSlug($uniqueSlug);
        $highHitTranslation = (new ArticleTranslation())
            ->setLocale('en')
            ->setTitle('Unique High Hits Title')
            ->setBody('Body')
            ->setDraft(false)
            ->setHit(99999)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTime())
            ->setCreatedBy($this->testUser)
            ->setUpdatedBy($this->testUser)
            ->setArticle($highHitArticle);

        $highHitArticle->addArticleTranslation($highHitTranslation);

        $this->entityManager->persist($highHitArticle);
        $this->entityManager->persist($highHitTranslation);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $topArticles = $this->articleRepository->getTopArticles(1, 'en');

        $this->assertNotEmpty($topArticles);
        $this->assertSame('Unique High Hits Title', $topArticles[0]->getArticleTranslations()->first()->getTitle());
    }

    public function testSearchAndGetCountWithSearchTermAndTagFilters(): void
    {
        $tag = new Tag();
        $tag->setName('unique_php_tag_'.uniqid());
        $this->entityManager->persist($tag);

        $uniqueSlug = 'searched-article-'.uniqid();
        $article = new Article();
        $article->setSlug($uniqueSlug)
            ->addTag($tag);

        $translation = (new ArticleTranslation())
            ->setLocale('en')
            ->setTitle('Unique Doctrine Searchable Title')
            ->setBody('Mastering custom DQL query builders')
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

        $searchArticle = new SearchArticle(
            tag: $tag->getName(),
            sorting: 'updatedAt',
            dir: Criteria::DESC,
            page: 1,
            search: 'Unique Doctrine Searchable',
            limit: 10,
        );

        $results = $this->articleRepository->search($searchArticle);

        $articleEntity = isset($results[0]['article']) ? $results[0]['article'] : $results[0];

        $this->assertNotEmpty($results);
        $this->assertSame('Unique Doctrine Searchable Title', $articleEntity->getArticleTranslations()->first()->getTitle());
    }

    public function testGetCountReturnsAccurateCountForSearchCriteria(): void
    {
        $tag = new Tag();
        $tag->setName('count_tag_'.uniqid());
        $this->entityManager->persist($tag);

        for ($i = 1; $i <= 2; ++$i) {
            $article = new Article();
            $article->setSlug('count-article-'.$i.'-'.uniqid())
                ->addTag($tag);

            $translation = (new ArticleTranslation())
                ->setLocale('en')
                ->setTitle('Searchable Title '.$i)
                ->setBody('Content')
                ->setDraft(false)
                ->setCreatedAt(new \DateTimeImmutable())
                ->setUpdatedAt(new \DateTime())
                ->setCreatedBy($this->testUser)
                ->setUpdatedBy($this->testUser)
                ->setArticle($article);

            $article->addArticleTranslation($translation);
            $this->entityManager->persist($article);
            $this->entityManager->persist($translation);
        }

        $otherArticle = new Article();
        $otherArticle->setSlug('other-article-'.uniqid());
        $otherTranslation = (new ArticleTranslation())
            ->setLocale('en')
            ->setTitle('Other Title')
            ->setBody('Content')
            ->setDraft(false)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTime())
            ->setCreatedBy($this->testUser)
            ->setUpdatedBy($this->testUser)
            ->setArticle($otherArticle);

        $otherArticle->addArticleTranslation($otherTranslation);
        $this->entityManager->persist($otherArticle);
        $this->entityManager->persist($otherTranslation);

        $this->entityManager->flush();
        $this->entityManager->clear();

        $searchArticle = new SearchArticle(
            tag: $tag->getName(),
            search: 'Searchable Title',
            limit: 10
        );

        $count = $this->articleRepository->getCount($searchArticle);

        $this->assertSame(2, $count);
    }

    public function testCountBlogArticlesReturnsCorrectScalarCount(): void
    {
        $locale = 'fr';

        $article1 = new Article();
        $article1->setSlug('fr-published-'.uniqid());
        $t1 = (new ArticleTranslation())
            ->setLocale($locale)
            ->setTitle('FR Published')
            ->setBody('Body')
            ->setDraft(false)
            ->setHit(10)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTime())
            ->setCreatedBy($this->testUser)
            ->setUpdatedBy($this->testUser)
            ->setArticle($article1);
        $article1->addArticleTranslation($t1);

        $article2 = new Article();
        $article2->setSlug('fr-draft-'.uniqid());
        $t2 = (new ArticleTranslation())
            ->setLocale($locale)
            ->setTitle('FR Draft')
            ->setBody('Body')
            ->setDraft(true)
            ->setHit(10)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTime())
            ->setCreatedBy($this->testUser)
            ->setUpdatedBy($this->testUser)
            ->setArticle($article2);
        $article2->addArticleTranslation($t2);

        $this->entityManager->persist($article1);
        $this->entityManager->persist($t1);
        $this->entityManager->persist($article2);
        $this->entityManager->persist($t2);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $count = $this->articleRepository->countBlogArticles($locale);

        $this->assertSame(1, $count);
    }
}
