<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

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
    public const PER_PAGE = 30;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * @throws Exception
     */
    public function search(Request $request): array
    {
        $query = $this->createBasicSearchQuery($request);
        $query->setMaxResults($request->get('limit', self::PER_PAGE));
        $query->setFirstResult(((int) $request->get('page', 1) - 1) * self::PER_PAGE);
        $query->groupBy('a.slug');

        $query->orderBy('a.'.$request->get('sorting', 'created_at'), $request->get('dir', Criteria::DESC));

        return $query->executeQuery()->fetchAllAssociative();
    }

    /**
     * @throws Exception
     */
    public function getCount(Request $request): int
    {
        $query = $this->createBasicSearchQuery($request);
        $query->select('count(a.slug)');

        $query->setMaxResults(1);

        return (int) $query->executeQuery()->fetchFirstColumn();
    }

    /**
     * Unfortunately I have to use Doctrine DBAL because I need MySQL function GROUP_CONCAT, which does not exist out
     * of the box in Doctrine ORM, and I'm too lazy to implement it via lexer:).
     */
    protected function createBasicSearchQuery(Request $request): QueryBuilder
    {
        $query = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $query->from($this->getEntityManager()->getClassMetadata(Article::class)->getTableName(), 'a');
        $query->select('a.*');
        $query->addSelect('GROUP_CONCAT(a.locale SEPARATOR \',\') as locale');

        if ($request->query->has('search')) {
            $query->andWhere(
                $query->expr()->or(
                    $query->expr()->like('a.title', ':search'),
                    $query->expr()->like('a.body', ':search')
                )
            )
            ->setParameter('search', '%'.$request->get('search').'%');
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
            ->setFirstResult(($page - 1) * $count)
            ->getQuery()
            ->getResult();
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function countBlogArticles(string $locale): int
    {
        return (int) $this->createBasicBlogQuery($locale)
            ->select('COUNT(a)')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleScalarResult();
    }

    protected function createBasicBlogQuery($locale): \Doctrine\ORM\QueryBuilder
    {
        return $this->createQueryBuilder('a')
            ->where('a.draft != :not_draft')
            ->andWhere('a.locale = :locale')
            ->setParameter('not_draft', true)
            ->setParameter('locale', $locale)
        ;
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
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

    public function getTranslatedLocales(string $slug): array
    {
        return $this->createQueryBuilder('a')
            ->select('a.locale')
            ->where('a.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getArrayResult();
    }
}
