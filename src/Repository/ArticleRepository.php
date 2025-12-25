<?php

namespace App\Repository;

use App\DTO\SearchArticle;
use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 *
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null, int|null $limit = null, int|null $offset = null)
 * @method Article|null findOneBySlug(string $slug, array<string, string>|null $orderBy = null)
 *
 * @seal-methods
 */
class ArticleRepository extends ServiceEntityRepository
{
    /**
     * @psalm-suppress PossiblyUnusedParam
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * @psalm-suppress PossiblyUnusedParam
     *
     * @return Article[]
     */
    public function search(SearchArticle $searchArticle): array
    {
        return $this->createBasicSearchQuery($searchArticle)
            ->select('a as article')
            ->setMaxResults($searchArticle->limit)
            ->setFirstResult(($searchArticle->page - 1) * $searchArticle->limit)
            ->addOrderBy('trans.'.$searchArticle->sorting, $searchArticle->dir)
            ->getQuery()
            ->getResult();
    }

    /**
     * @psalm-suppress PossiblyUnusedParam
     */
    public function getCount(SearchArticle $searchArticle): int
    {
        return $this->createBasicSearchQuery($searchArticle)
            ->select('count(DISTINCT a.id)')
            ->distinct()
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @psalm-suppress PossiblyUnusedParam
     * @psalm-suppress PossiblyUnusedMethod
     */
    protected function createBasicSearchQuery(SearchArticle $searchArticle): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('a')
            ->innerJoin('a.articleTranslations', 'trans')
        ;

        $this->addSearchFilter($queryBuilder, $searchArticle->search);
        $this->addTagFilter($queryBuilder, $searchArticle->tag);

        return $queryBuilder;
    }

    private function addSearchFilter(QueryBuilder $queryBuilder, ?string $search): void
    {
        if ($search) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->like('trans.title', ':search'),
                    $queryBuilder->expr()->like('trans.body', ':search')
                )
            )
                ->setParameter('search', '%'.$search.'%');
        }
    }

    private function addTagFilter(QueryBuilder $queryBuilder, ?string $tag): void
    {
        if ($tag) {
            $queryBuilder
                ->innerJoin('a.tags', 't')
                ->andWhere('t.name = :tag')
                ->setParameter('tag', $tag);
        }
    }

    /**
     * @psalm-suppress PossiblyUnusedParam
     *
     * @return Article[]
     */
    public function getTopArticles(int $count, string $locale): array
    {
        return $this->createBasicBlogQuery($locale)
            ->orderBy('trans.hit', 'DESC')
            ->addOrderBy('trans.updatedAt', 'DESC')
            ->setMaxResults($count)
            ->getQuery()
            ->getResult();
    }

    /**
     * @psalm-suppress PossiblyUnusedParam
     *
     * @return Article[]
     */
    public function getBlogArticles(int $count, int $page, string $locale): array
    {
        return $this->createBasicBlogQuery($locale)
            ->addOrderBy('trans.updatedAt', 'DESC')
            ->setMaxResults($count)
            ->setFirstResult(($page - 1) * $count)
            ->getQuery()
            ->getResult();
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     *
     * @psalm-suppress PossiblyUnusedParam
     */
    public function countBlogArticles(string $locale): int
    {
        return (int) $this->createBasicBlogQuery($locale)
            ->select('COUNT(DISTINCT a.id)')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @psalm-suppress PossiblyUnusedParam
     * @psalm-suppress PossiblyUnusedMethod
     */
    protected function createBasicBlogQuery(string $locale): QueryBuilder
    {
        return $this->createQueryBuilder('a')
            ->innerJoin('a.articleTranslations', 'trans')
            ->where('trans.draft != :not_draft')
            ->andWhere('trans.locale = :locale')
            ->setParameter('not_draft', true)
            ->setParameter('locale', $locale)
        ;
    }
}
