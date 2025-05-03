<?php

namespace App\DataFixtures\Tests;

use App\Entity\Article;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ArticleFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['tests'];
    }

    public function load(ObjectManager $manager): void
    {
        $article = new Article();
        $article->setSlug('test_slug')
            ->addTag($this->getReference('tag_apple'))
            ->addTag($this->getReference('tag_banana'))
            ->addTag($this->getReference('tag_plum'));

        $manager->persist($article);
        $manager->flush();

        $this->setReference('article_test', $article);
    }

    public function getDependencies(): array
    {
        return [
            TagFixtures::class,
        ];
    }
}
