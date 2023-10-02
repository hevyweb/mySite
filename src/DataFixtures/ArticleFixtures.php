<?php

namespace App\DataFixtures;

use App\Entity\Article;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;

class ArticleFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(private array $locales)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = FakerFactory::create();
        for($n = 0; $n < 100; $n++) {
            $article = new Article();
            $article
                ->setLocale($faker->randomElement($this->locales))
                ->setCreatedAt(new \DateTimeImmutable($faker->dateTimeBetween->format('c')))
                ->setUpdatedAt($faker->boolean() ? $faker->dateTimeBetween() : null)
                ->setTitle($faker->text(128))
                ->setUpdatedBy($article->getUpdatedAt() ? $this->getReference('user_admin') : null)
                ->setCreatedBy($this->getReference('user_admin'))
                ->setBody($faker->randomHtml())
                ->setDraft($faker->boolean())
                ->setHit($faker->numberBetween(0, 999))
                ->setPreview($faker->text(256))
                ->setSlug($faker->slug())
                ->setTags($faker->words($faker->numberBetween(0, 5)))
                ->setImage('../images/notfound.jpg')
            ;
            $manager->persist($article);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
        ];
    }
}
