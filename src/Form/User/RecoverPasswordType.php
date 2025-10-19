<?php

namespace App\Form\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @psalm-suppress MissingTemplateParam
 */
class RecoverPasswordType extends AbstractType
{
    public function __construct(public readonly TranslatorInterface $translator)
    {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'required' => true,
                'label' => $this->translator->trans('Email', [], 'user'),
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'placeholder' => $this->translator->trans('Email', [], 'user'),
                    'class' => 'form-control',
                    'maxlength' => 64,
                ],
                'constraints' => [
                    new NotBlank(),
                    new Email(),
                    new Length([
                        'max' => 64,
                    ]),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->translator->trans('Recover password', [], 'user'),
                'attr' => [
                    'class' => 'btn btn-primary submit-btn mt-3',
                ],
            ]);
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): OptionsResolver
    {
        return $resolver->setDefaults([
            'csrf_protection' => true,
            'csrf_field_name' => '_csrf_token',
            'csrf_token_id' => 'recover_password',
        ]);
    }
}
