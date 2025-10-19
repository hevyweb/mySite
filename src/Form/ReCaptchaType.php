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
    #[\Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['attr']['style'] = 'display: none';
    }

    #[\Override]
    public function getParent(): ?string
    {
        return TextareaType::class;
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'recaptcha';
    }
}
