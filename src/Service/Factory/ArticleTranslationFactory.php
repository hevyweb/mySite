<?php

namespace App\Service\Factory;

use App\Entity\Article;
use App\Entity\ArticleTranslation;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;

readonly class ArticleTranslationFactory implements FactoryInterface
{
    public function __construct(
        private Security $security,
        private Request $request,
    )
    {
    }

    public function build(): ArticleTranslation
    {
        $article = new Article();
        $translation = new ArticleTranslation();
        $translation->setLocale($this->request->getLocale())
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTime())
            ->setCreatedBy($this->security->getUser())
            ->setUpdatedBy($this->security->getUser());
        $article->addArticleTranslation($translation);
        return $translation;
    }
}