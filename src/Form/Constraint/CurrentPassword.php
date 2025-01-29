<?php

namespace App\Form\Constraint;

use App\Form\ConstraintValidator\CurrentPasswordValidator;
use Symfony\Component\Validator\Constraint;

class CurrentPassword extends Constraint
{
    public string $message = 'Current password is incorrect.';

    public function validatedBy(): string
    {
        return CurrentPasswordValidator::class;
    }
}