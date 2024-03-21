<?php

namespace App\Form\ConstraintValidator;

use ReCaptcha\ReCaptcha;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ReCaptchaValidator extends ConstraintValidator
{
    public function __construct(private readonly ReCaptcha $reCaptcha)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        $response = $this->reCaptcha->verify($value);

        if (!$response->isSuccess()) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
