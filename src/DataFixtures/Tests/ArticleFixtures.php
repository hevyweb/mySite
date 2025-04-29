<?php

namespace App\DataFixtures\Tests;

use App\DataFixtures\TagFixture;
use App\Entity\Article;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ArticleFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{    
    public function load(ObjectManager $manager): void
    {        
        $article = new Article();
        $article->setSlug('test_slug')
            ->addTag($this->getReference('tag_apple'))
            ->addTag($this->getReference('tag_banana'))
            ->addTag($this->getReference('tag_plum'))
            ->addArticleTranslation($this->getReference('article_translation_test'))
        ;
    }

    public function getDependencies(): array
    {
        return [
            ArticleTranslationFixtures::class,
            TagFixture::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['tests'];
    }
}
