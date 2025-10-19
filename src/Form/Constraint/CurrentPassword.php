<?php

namespace App\Form\Constraint;

use App\Form\ConstraintValidator\CurrentPasswordValidator;
use Symfony\Component\Validator\Constraint;

class CurrentPassword extends Constraint
{
    protected string $message;

    #[\Override]
    public function validatedBy(): string
    {
        return CurrentPasswordValidator::class;
    }
}
