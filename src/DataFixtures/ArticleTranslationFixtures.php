<?php

namespace App\DataFixtures;

use App\Entity\ArticleTranslation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;

class ArticleTranslationFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(private readonly array $locales)
    {
    }

    /**
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        $manager->getConnection()->getConfiguration()->setSQLLogger(null);
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
                    ->setUpdatedBy($this->getReference('user_admin'))
                    ->setCreatedBy($this->getReference('user_admin'))
                    ->setDraft($faker->boolean())
                    ->setHit($faker->numberBetween(0, 999))
                    ->setImage('images/notfound.jpg')
                    ->setArticle($this->getReference('article_'.$n));

                $manager->persist($articleTranslation);
                $manager->flush();
                $manager->clear();
            }
        }
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            ArticleFixtures::class,
        ];
    }
}
