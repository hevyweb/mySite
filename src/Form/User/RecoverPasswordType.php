<?php

namespace App\Form\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class RecoverPasswordType extends AbstractType
{
    public function __construct(readonly TranslatorInterface $translator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'required' => true,
                'label' => $this->translator->trans('Email', [], 'user'),
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'placeholder' => $this->translator->trans('Email', [], 'user'),
                ],
            ]);
    }
}
