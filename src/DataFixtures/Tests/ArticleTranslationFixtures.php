<?php

namespace App\DataFixtures\Tests;

use App\Entity\Article;
use App\Entity\ArticleTranslation;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ArticleTranslationFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const ARTICLE_TRANSLATION_ID = 1;

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $articleTranslation = new ArticleTranslation();

        $articleTranslation
            ->setLocale('en')
            ->setTitle('Translation title')
            ->setBody('<b>Translation body</b>')
            ->setPreview('Translation preview')
            ->setCreatedAt(new \DateTimeImmutable('2022-02-24'))
            ->setUpdatedAt(new \DateTime('2022-02-24'))
            ->setUpdatedBy($this->getReference('test_admin', User::class))
            ->setCreatedBy($this->getReference('test_admin', User::class))
            ->setDraft(false)
            ->setHit(456)
            ->setImage('test_image.jpg')
            ->setArticle($this->getReference('article_test', Article::class));

        $manager->persist($articleTranslation);
        $manager->flush();
        
        $this->addReference('article_translation_test', $articleTranslation);
    }

    #[\Override]
    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            ArticleFixtures::class,
        ];
    }

    #[\Override]
    public static function getGroups(): array
    {
        return ['tests'];
    }
}
