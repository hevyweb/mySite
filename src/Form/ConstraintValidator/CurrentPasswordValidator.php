<?php

namespace App\Form\ConstraintValidator;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CurrentPasswordValidator extends ConstraintValidator
{
    public function __construct(
        private readonly Security $security,
        private readonly UserPasswordHasherInterface $hasher,
    )
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$this->hasher->isPasswordValid($this->security->getUser(), $value ?? '')) {
            $this->context->buildViolation($constraint->message)
                ->setTranslationDomain('user')
                ->addViolation();
        }
    }
}