<?php

namespace App\Form\ConstraintValidator;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CurrentPasswordValidator extends ConstraintValidator
{
    private string $message = 'Current password is incorrect.';

    public function __construct(
        private readonly Security $security,
        private readonly UserPasswordHasherInterface $hasher,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        /**
         * @var User $user
         */
        $user = $this->security->getUser();
        if (!$this->hasher->isPasswordValid($user, $value ?? '')) {
            $this->context->buildViolation($this->message)
                ->setTranslationDomain('user')
                ->addViolation();
        }
    }
}
