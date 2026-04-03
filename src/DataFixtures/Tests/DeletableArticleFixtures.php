<?php

namespace App\DataFixtures\Tests;

use App\Entity\Article;
use App\Entity\ArticleTranslation;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class DeletableArticleFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    #[\Override]
    public static function getGroups(): array
    {
        return ['tests'];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        // Article for single deletion
        $article1 = new Article();
        $article1->setSlug('deletable-article-1');
        $translation1 = new ArticleTranslation();
        $translation1->setLocale('en')
            ->setTitle('Deletable Article One')
            ->setBody('Body of deletable article one')
            ->setPreview('Preview of deletable article one')
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTime())
            ->setUpdatedBy($this->getReference('test_admin', User::class))
            ->setCreatedBy($this->getReference('test_admin', User::class))
            ->setDraft(false);
        $article1->addArticleTranslation($translation1);
        $manager->persist($article1);
        $this->addReference('deletable_article_1', $article1);

        // Article for multiple deletion
        $article2 = new Article();
        $article2->setSlug('deletable-article-2');
        $translation2 = new ArticleTranslation();
        $translation2->setLocale('en')
            ->setTitle('Deletable Article Two')
            ->setBody('Body of deletable article two')
            ->setPreview('Preview of deletable article two')
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTime())
            ->setUpdatedBy($this->getReference('test_admin', User::class))
            ->setCreatedBy($this->getReference('test_admin', User::class))
            ->setDraft(false);
        $article2->addArticleTranslation($translation2);
        $manager->persist($article2);
        $this->addReference('deletable_article_2', $article2);

        $manager->flush();
    }

    #[\Override]
    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
