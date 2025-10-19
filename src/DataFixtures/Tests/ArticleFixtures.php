<?php

namespace App\DataFixtures\Tests;

use App\Entity\Article;
use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ArticleFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    #[\Override]
    public static function getGroups(): array
    {
        return ['tests'];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $article = new Article();
        $article->setSlug('test_slug')
            ->addTag($this->getReference('tag_apple', Tag::class))
            ->addTag($this->getReference('tag_banana', Tag::class))
            ->addTag($this->getReference('tag_plum', Tag::class));

        $manager->persist($article);
        $manager->flush();

        $this->setReference('article_test', $article);
    }

    #[\Override]
    public function getDependencies(): array
    {
        return [
            TagFixtures::class,
        ];
    }
}
