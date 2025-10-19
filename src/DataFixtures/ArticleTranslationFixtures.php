<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\ArticleTranslation;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;

class ArticleTranslationFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    /**
     * @param array<string> $locales
     */
    public function __construct(private readonly array $locales)
    {
    }

    /**
     * @throws \Exception
     */
    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $fakers['en'] = FakerFactory::create();
        $fakers['ua'] = FakerFactory::create('uk_UA');
        for ($n = 0; $n < 100; ++$n) {
            $languagesCount = count($this->locales);
            $locales = $fakers['en']->shuffleArray($this->locales);
            for ($l = 0; $l < $fakers['en']->numberBetween(0, $languagesCount - 1); ++$l) {
                array_pop($locales);
            }
            foreach ($locales as $locale) {
                $faker = $fakers[$locale];
                $articleTranslation = new ArticleTranslation();

                $articleTranslation
                    ->setLocale($locale)
                    ->setTitle($faker->text(128))
                    ->setBody($faker->randomHtml())
                    ->setPreview($faker->text(256))
                    ->setCreatedAt(new \DateTimeImmutable($faker->dateTimeBetween->format('c')))
                    ->setUpdatedAt($faker->dateTimeBetween())
                    ->setUpdatedBy($this->getReference('user_admin', User::class))
                    ->setCreatedBy($this->getReference('user_admin', User::class))
                    ->setDraft($faker->boolean())
                    ->setHit($faker->numberBetween(0, 999))
                    ->setImage('images/notfound.jpg')
                    ->setArticle($this->getReference('article_'.$n, Article::class));

                $manager->persist($articleTranslation);
                $manager->flush();
                $manager->clear();
            }
        }
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
        return ['default'];
    }
}
