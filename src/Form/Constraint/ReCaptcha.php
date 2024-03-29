<?php

namespace App\Form\Constraint;

use App\Form\ConstraintValidator\ReCaptchaValidator;
use Symfony\Component\Validator\Constraint;

class ReCaptcha extends Constraint
{
    public string $message = 'Captcha validation error.';

    public function validatedBy(): string
    {
        return ReCaptchaValidator::class;
    }
}
