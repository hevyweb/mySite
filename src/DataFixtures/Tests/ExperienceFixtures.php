<?php

namespace App\DataFixtures\Tests;

use App\Entity\Experience;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class ExperienceFixtures extends Fixture implements FixtureGroupInterface
{
    #[\Override]
    public static function getGroups(): array
    {
        return ['tests'];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $this->buildTenEntries($manager, 'en');
        $this->buildTenEntries($manager, 'uk');
    }

    private function buildTenEntries(ObjectManager $manager, string $locale): void
    {
        $baseDate = new \DateTime('2015-01-01');
        $descriptions = [
            'en' => [
                'Senior web developer with expertise in Symfony and React frameworks',
                'Full-stack developer experienced in PHP, JavaScript, and database design',
                'Technical lead responsible for architecture and team guidance',
                'DevOps engineer with experience in Docker and Kubernetes',
                'Database administrator managing large-scale data systems',
                'Frontend developer with focus on React and modern JavaScript',
                'Backend developer specializing in API development and microservices',
                'Full-stack developer with 8+ years experience',
                'QA engineer ensuring code quality and automated testing',
                'Solution architect designing enterprise systems',
            ],
            'uk' => [
                'Старший веб-розробник з досвідом роботи з Symfony та React',
                'Full-stack розробник з досвідом PHP, JavaScript та дизайну баз даних',
                'Технічний лідер, відповідальний за архітектуру та керівництво командою',
                'DevOps інженер з досвідом Docker та Kubernetes',
                'Адміністратор бази даних, керування великомасштабними системами',
                'Frontend розробник з фокусом на React та сучасний JavaScript',
                'Backend розробник, спеціалізується на розробці API та мікросервісів',
                'Full-stack розробник з 8+ років досвіду',
                'QA інженер, що забезпечує якість коду та автоматичне тестування',
                'Архітектор рішень, проектування корпоративних систем',
            ],
        ];

        $companies = [
            'en' => [
                'Tech Company Inc',
                'Digital Solutions Ltd',
                'Web Systems Pro',
                'Cloud Services Co',
                'Data Analytics Group',
                'Mobile Apps Factory',
                'Enterprise Solutions',
                'Innovation Partners',
                'Software Development House',
                'Tech Solutions Global',
            ],
            'uk' => [
                'Технологічна компанія',
                'Цифрові рішення Ltd',
                'Веб-системи Pro',
                'Хмарні послуги Co',
                'Група аналітики даних',
                'Фабрика мобільних додатків',
                'Корпоративні рішення',
                'Партнери інновацій',
                'Дім розроблення ПЗ',
                'Глобальні технорішення',
            ],
        ];

        $locations = [
            'en' => [
                'San Francisco, USA',
                'New York, USA',
                'London, UK',
                'Berlin, Germany',
                'Amsterdam, Netherlands',
                'Toronto, Canada',
                'Sydney, Australia',
                'Singapore',
                'Tokyo, Japan',
                'Dubai, UAE',
            ],
            'uk' => [
                'Сан-Франциско, США',
                'Нью-Йорк, США',
                'Лондон, Великобританія',
                'Берлін, Німеччина',
                'Амстердам, Нідерланди',
                'Торонто, Канада',
                'Сідней, Австралія',
                'Сінгапур',
                'Токіо, Японія',
                'Дубай, ОАЕ',
            ],
        ];

        $titles = [
            'en' => [
                'Senior Developer',
                'Developer',
                'Lead Developer',
                'DevOps Engineer',
                'Database Administrator',
                'Frontend Developer',
                'Backend Developer',
                'Full Stack Developer',
                'QA Engineer',
                'Solution Architect',
            ],
            'uk' => [
                'Старший розробник',
                'Розробник',
                'Технічний лідер',
                'DevOps Інженер',
                'Адміністратор БД',
                'Frontend Розробник',
                'Backend Розробник',
                'Full Stack Розробник',
                'QA Інженер',
                'Архітектор рішень',
            ],
        ];

        for ($n = 0; $n < 10; ++$n) {
            $experience = new Experience();
            $experience->setLocale($locale)
                ->setCompany($companies[$locale][$n])
                ->setDescription($descriptions[$locale][$n])
                ->setName($titles[$locale][$n])
                ->setFromDate((clone $baseDate)->modify("+{$n} years"))
                ->setToDate((clone $baseDate)->modify("+".($n + 2)." years"))
                ->setLocation($locations[$locale][$n])
                ->setImage('no-logo.png')
            ;
            $manager->persist($experience);
        }

        $manager->flush();
        $manager->clear();
        gc_collect_cycles();
    }
}

