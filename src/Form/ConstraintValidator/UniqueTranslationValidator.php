<?php

namespace App\Form\ConstraintValidator;

use App\Entity\ArticleTranslation;
use App\Form\Constraint\UniqueTranslation;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueTranslationValidator extends ConstraintValidator
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        $article = $this->context->getRoot()->get('article');
        $newArticleTranslation = $this->context->getRoot()->get('translation')->getData();

        try {
            $existingArticleTranslation = $this->entityManager->getRepository(ArticleTranslation::class)
                ->getTranslationBySlug($value, $article->get('slug')->getData());

            if ($newArticleTranslation->getId != $existingArticleTranslation->getId()) {
                $this->context
                    ->buildViolation(UniqueTranslation::$message)
                    ->atPath('translation.locale')
                    ->addViolation();
            }
        } catch (NoResultException $exception) {
            $this->logger->debug('No duplicated detected.');
        }
    }
}
