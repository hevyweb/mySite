<?php

namespace App\Service\Factory;

use App\Entity\Article;
use App\Entity\ArticleTranslation;
use App\Entity\User;
use App\Exception\IncorrectUserException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @template-implements FactoryInterface<ArticleTranslation>
 */
readonly class ArticleTranslationFactory implements FactoryInterface
{
    public function __construct(
        private Security $security,
        private Request $request,
    ) {
    }

    /**
     * @throws IncorrectUserException
     */
    #[\Override]
    public function build(): ArticleTranslation
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw new IncorrectUserException('User is not instance of User');
        }

        $article = new Article();
        $translation = new ArticleTranslation();
        $translation->setLocale($this->request->getLocale())
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTime())
            ->setCreatedBy($user)
            ->setUpdatedBy($user);
        $article->addArticleTranslation($translation);

        return $translation;
    }
}
