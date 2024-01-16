<?php

namespace App\Form\ConstraintValidator;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueSlugValidator extends ConstraintValidator
{
    public function __construct(
        private EntityManagerInterface $entityManager
    )
    {

    }

    public function validate(mixed $value, Constraint $constraint)
    {
        $this->entityManager->getRepository(Article::class)
            ->findDuplicates($value, $this->context->getRoot());
    }
}