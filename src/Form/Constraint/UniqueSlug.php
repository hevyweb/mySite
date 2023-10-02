<?php

namespace App\Form\Constraint;

use Symfony\Component\Validator\Constraint;

class UniqueSlug extends Constraint
{
    public string $message = 'Article with such slug already exists.';
    // If the constraint has configuration options, define them as public properties
    public string $mode = 'strict';
}