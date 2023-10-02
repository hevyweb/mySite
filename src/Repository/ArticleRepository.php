<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query\Expr\OrderBy;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @extends ServiceEntityRepository<Article>
 *
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    const PER_PAGE = 30;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public function search(Request $request)
    {
        $query = $this->createBasicSearchQuery($request);

        $query->setMaxResults($request->get('limit', self::PER_PAGE));
        $query->setFirstResult(((int) $request->get('page', 1) - 1) * self::PER_PAGE);

        $query->orderBy('a.'.$request->get('sorting', 'createdAt'), $request->get('dir', Criteria::DESC));

        return $query->getQuery()->getResult();
    }

    public function getCount(Request $request): int
    {
        $query = $this->createBasicSearchQuery($request);
        $query->select('count(a)');

        $query->setMaxResults(1);

        return (int) $query->getQuery()->getSingleScalarResult();
    }

    protected function createBasicSearchQuery(Request $request): QueryBuilder
    {
        $query = $this->createQueryBuilder('a');

        if ($request->query->has('search')) {
            $query->andWhere(
                $query->expr()->orX(
                    $query->expr()->like('a.title', ':search'),
                    $query->expr()->like('a.body', ':search')
                )
            )
            ->setParameter('search', '%' . $request->get('search') . '%');
        }

        return $query;
    }

    public function getTopArticles(int $count, string $locale): array
    {
        return $this->createBasicBlogQuery($locale)
            ->orderBy('a.hit', 'DESC')
            ->addOrderBy('a.createdAt', 'DESC')
            ->setMaxResults($count)
            ->getQuery()
            ->getResult();
    }

    public function getBlogArticles(int $count, int $page, string $locale): array
    {
        return $this->createBasicBlogQuery($locale)
            ->addOrderBy('a.createdAt', 'DESC')
            ->setMaxResults($count)
            ->setFirstResult(($page -1)*$count)
            ->getQuery()
            ->getResult();
    }

    public function countBlogArticles(string $locale): int
    {
        return (int) $this->createBasicBlogQuery($locale)
            ->select('COUNT(a)')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleScalarResult();
    }

    protected function createBasicBlogQuery($locale): QueryBuilder
    {
        return $this->createQueryBuilder('a')
            ->where('a.draft != :not_draft')
            ->andWhere('a.locale = :locale')
            ->setParameter('not_draft', true)
            ->setParameter('locale', $locale)
        ;
    }

    public function findArticleWithSubstitutes(string $slug, string $locale): ?Article
    {
        return $this->createQueryBuilder('a')
            ->select('a')
            ->addSelect('(CASE WHEN a.locale = :locale THEN 1 ELSE 2END) AS HIDDEN ORD')
            ->where('a.slug = :slug')
            ->orderBy('ORD', Criteria::ASC)
            ->setParameter('slug', $slug)
            ->setParameter('locale', $locale)
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();
    }

}
