<?php

namespace App\DataFixtures;

use App\Entity\Article;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;
use Faker\Generator;

class ArticleFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    private Generator $faker;

    /**
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        $this->faker = FakerFactory::create();
        for ($n = 0; $n < 100; ++$n) {
            $article = new Article();
            $article
                 ->setSlug($this->faker->slug())
            ;

            $this->addTags($article);
            $manager->persist($article);
            $this->setReference('article_'.$n, $article);
        }

        $manager->flush();
        $manager->clear();
    }

    private function addTags(Article $article): void
    {
        for ($n = 0; $n < $this->faker->numberBetween(0, 3); ++$n) {
            $tag = $this->getReference('tag_'.$this->faker->numberBetween(0, 99));
            $article->addTag($tag);
        }
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            TagFixture::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['default'];
    }
}
