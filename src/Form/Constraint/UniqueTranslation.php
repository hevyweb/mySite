<?php

namespace App\Form\Constraint;

use App\Form\ConstraintValidator\UniqueTranslationValidator;
use Symfony\Component\Validator\Constraint;

class UniqueTranslation extends Constraint
{
    public static string $message = 'Article with such slug already exists.';

    public function validatedBy(): string
    {
        return UniqueTranslationValidator::class;
    }
}
