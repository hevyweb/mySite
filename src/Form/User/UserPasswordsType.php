<?php

namespace App\Form\User;

use App\Form\Constraint\CurrentPassword;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Sequentially;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @psalm-suppress MissingTemplateParam
 */
class UserPasswordsType extends AbstractType
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currentPassword', PasswordType::class, [
                'label' => $this->translator->trans('Current password', [], 'user'),
                'mapped' => false,
                'attr' => [
                    'maxlength' => 32,
                ],
                'constraints' => [
                    new Sequentially([
                        'constraints' => [
                            new NotBlank(),
                            new Length([
                                'max' => 32,
                            ]),
                            new CurrentPassword(),
                        ],
                    ]),
                ],
            ])
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => $this->translator->trans('The password fields must match.', [], 'user'),
                'first_options' => [
                    'label' => $this->translator->trans('New password', [], 'user'),
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ],
                ],
                'second_options' => [
                    'label' => $this->translator->trans('Repeat new password', [], 'user'),
                    'attr' => [
                        'autocomplete' => 'confirm-password',
                    ],
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'max' => 32,
                    ]),
                ],
                'required' => true,
            ]);
    }
}
