<?php

namespace App\Repository;

use App\Entity\ArticleTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ArticleTranslation>
 *
 * @method ArticleTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArticleTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArticleTranslation[]    findAll()
 * @method ArticleTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @seal-methods
 */
class ArticleTranslationRepository extends ServiceEntityRepository
{
    /**
     * @psalm-suppress PossiblyUnusedParam
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArticleTranslation::class);
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     *
     * @psalm-suppress PossiblyUnusedParam
     */
    public function getTranslationBySlug(string $slug, string $locale): ArticleTranslation
    {
        return $this->createQueryBuilder('t')
            ->innerJoin('t.article', 'a')
            ->where('a.slug = :slug')
            ->andWhere('t.locale = :locale')
            ->setParameters([
                'slug' => $slug,
                'locale' => $locale,
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();
    }
}
