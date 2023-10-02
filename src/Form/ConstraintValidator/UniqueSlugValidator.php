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
        print_r($this->context->getRoot());exit;
        $this->entityManager->getRepository(Article::class)
            ->findDuplicates($value, $this->context->getRoot());
    }
}