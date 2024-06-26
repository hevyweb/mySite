<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * @psalm-suppress MissingTemplateParam
 */
class ReCaptchaType extends AbstractType
{
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['attr']['style'] = 'display: none';
    }

    public function getParent(): ?string
    {
        return TextareaType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'recaptcha';
    }
}
