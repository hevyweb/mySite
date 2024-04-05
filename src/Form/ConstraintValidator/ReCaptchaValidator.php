<?php

namespace App\Form\ConstraintValidator;

use App\Form\Constraint\ReCaptcha as ReCaptchaConstraint;
use ReCaptcha\ReCaptcha;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ReCaptchaValidator extends ConstraintValidator
{
    public function __construct(private readonly ReCaptcha $reCaptcha)
    {
    }

    public function validate(mixed $value, Constraint|ReCaptchaConstraint $constraint): void
    {
        /**
         * @psalm-var ReCaptchaConstraint $constraint
         */
        $response = $this->reCaptcha->verify($value);

        if (!$response->isSuccess()) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
