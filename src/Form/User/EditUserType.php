<?php

namespace App\Form\User;

use App\Entity\User;
use App\Type\Gender;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @psalm-suppress MissingTemplateParam
 */
class EditUserType extends AbstractType
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => $this->translator->trans('Email', [], 'user'),
                'help' => $this->translator->trans('After changing your email address, you will receive an email to both your old and new email addresses. The changes will only be applied after you confirm both emails.', [], 'user'),
                'attr' => [
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
            ->add('firstName', TextType::class, [
                'label' => $this->translator->trans('First name', [], 'user'),
                'required' => true,
                'attr' => [
                    'minlength' => 2,
                    'maxlength' => 32,
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
                    'class' => 'js-datepicker',
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
                    $this->translator->trans('Female', [], 'user') => Gender::FEMALE
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
