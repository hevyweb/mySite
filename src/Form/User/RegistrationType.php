<?php

namespace App\Form\User;

use App\Entity\User;
use App\Type\Gender;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
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

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => $this->translator->trans('Username', [], 'user'),
                'attr' => [
                    'maxlength' => 32,
                    'minlength' => 3,
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'max' => 32,
                        'min' => 3,
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
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank(),
                    new Email(),
                    new Length([
                        'max' => 64,
                    ]),
                ],
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'first_options' => [
                    'label' => $this->translator->trans('Password', [], 'user'),
                    'attr' => [
                        'maxlength' => 32,
                        'minlength' => 8,
                        'class' => 'form-control',
                    ],
                    'constraints' => [
                        new Length([
                            'max' => 32,
                            'min' => 8,
                        ]),
                    ],
                ],
                'second_options' => [
                    'label' => $this->translator->trans('Repeat password', [], 'user'),
                    'attr' => [
                        'maxlength' => 32,
                        'minlength' => 8,
                        'class' => 'form-control',
                    ],
                    'constraints' => [
                        new Length([
                            'max' => 32,
                            'min' => 8,
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
                    'minlength' => 2,
                    'maxlength' => 32,
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 2,
                        'max' => 32,
                    ]),
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => $this->translator->trans('Last name', [], 'user'),
                'required' => true,
                'attr' => [
                    'minlength' => 2,
                    'maxlength' => 32,
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 2,
                        'max' => 32,
                    ]),
                ],
            ])
            ->add('birthday', BirthdayType::class, [
                'html5' => false,
                'attr' => [
                    'class' => 'js-datepicker form-control',
                    'placeholder' => $this->translator->trans($_SERVER['DEFAULT_DATE_FORMAT']),
                    'maxlength' => 10,
                    'autocomplete' => 'off',
                ],
                'required' => false,
                'format' => $_SERVER['DEFAULT_DATE_FORMAT'],
                'label' => $this->translator->trans('Birthday', [], 'user'),
                'constraints' => [
                    new GreaterThanOrEqual([
                        'value' => '-100 years',
                        'message' => $this->translator->trans('You cannot be older than 100 years.', [], 'user'),
                    ]),
                    new LessThanOrEqual([
                        'value' => '-7 years',
                        'message' => $this->translator->trans('You must be at least 7 years old.', [], 'user'),
                    ]),
                ],
            ])
            ->add('sex', ChoiceType::class, [
                'choices' => [
                    $this->translator->trans('Male', [], 'user') => Gender::MALE,
                    $this->translator->trans('Female', [], 'user') => Gender::FEMALE,
                ],
                'attr' => [
                    'class' => 'form-control',
                ],
                'required' => false,
                'label' => $this->translator->trans('Sex', [], 'user'),
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->translator->trans('Sign up'),
                'attr' => [
                    'class' => 'btn btn-primary submit-btn',
                ],
            ]);
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_csrf_token',
            'csrf_token_id' => 'registration',
        ]);
    }
}
