<?php

namespace App\Form\User;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @psalm-suppress MissingTemplateParam
 */
class RegistrationType extends AbstractType
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => $this->translator->trans('Username', [], 'user'),
                'attr' => [
                    'maxlength' => 32,
                ],
                'constraints' => [
                    new Length([
                        'max' => 32,
                    ]),
                    new Regex([
                        'pattern' => '/^[\w\d\-_]+$/',
                        'message' => $this->translator->trans('Username should contain only letters, numbers, minus sign or underscore.', [], 'user'),
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => $this->translator->trans('Email', [], 'user'),
                'attr' => [
                    'maxlength' => 64,
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'max' => 64,
                    ]),
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => $this->translator->trans('Password', [], 'user'),
                    'attr' => [
                        'maxlength' => 32,
                    ],
                    'constraints' => [
                        new Length([
                            'max' => 32,
                        ]),
                    ],
                ],
                'second_options' => [
                    'label' => $this->translator->trans('Repeat password', [], 'user'),
                    'attr' => [
                        'maxlength' => 32,
                    ],
                    'constraints' => [
                        new Length([
                            'max' => 32,
                        ]),
                    ],
                ],
                'constraints' => [
                    new NotBlank(),
                ],
                'required' => true,
                'invalid_message' => $this->translator->trans('The password fields must match.', [], 'user'),
            ])
            ->add('firstName', TextType::class, [
                'label' => $this->translator->trans('First name', [], 'user'),
                'required' => true,
                'attr' => [
                    'maxlength' => 32,
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'max' => 32,
                    ]),
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => $this->translator->trans('Last name', [], 'user'),
                'required' => true,
                'attr' => [
                    'maxlength' => 32,
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'max' => 32,
                    ]),
                ],
            ])
            ->add('birthday', BirthdayType::class, [
                'html5' => false,
                'attr' => [
                    'class' => 'js-datepicker',
                    'placeholder' => $this->translator->trans($_SERVER['DEFAULT_DATE_FORMAT']),
                    'maxlength' => 10,
                    'autocomplete' => 'off',
                ],
                'required' => false,
                'format' => $_SERVER['DEFAULT_DATE_FORMAT'],
                'label' => $this->translator->trans('Birthday', [], 'user'),
            ])
            ->add('sex', ChoiceType::class, [
                'choices' => [
                    $this->translator->trans('Male', [], 'user') => 1,
                    $this->translator->trans('Female', [], 'user') => 2,
                ],
                'required' => false,
                'label' => $this->translator->trans('Sex', [], 'user'),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
