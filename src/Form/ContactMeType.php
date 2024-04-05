<?php

namespace App\Form;

use App\Entity\Message;
use App\Form\Constraint\ReCaptcha;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends AbstractType<string>
 */
class ContactMeType extends AbstractType
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'label' => $this->translator->trans('Your name', [], 'contactme'),
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'attr' => [
                    'id' => 'name',
                    'class' => 'form-control',
                    'maxlength' => 64,
                    'placeholder' => $this->translator->trans('Your name', [], 'contactme'),
                    'aria-describedby' => 'invalidNameFeedback',
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'max' => 64,
                    ]),
                ],
                'help' => $this->translator->trans('Please enter your name', [], 'contactme'),
                'help_attr' => [
                    'class' => 'invalid-feedback',
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => $this->translator->trans('Your email', [], 'contactme'),
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'attr' => [
                    'maxlength' => 128,
                    'id' => 'email',
                    'class' => 'form-control',
                    'placeholder' => $this->translator->trans('Your email', [], 'contactme'),
                    'aria-describedby' => 'invalidEmailFeedback',
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'max' => 128,
                    ]),
                ],
                'help' => $this->translator->trans('Please enter valid email', [], 'contactme'),
                'help_attr' => [
                    'class' => 'invalid-feedback',
                ],
            ])
            ->add('subject', TextType::class, [
                'label' => $this->translator->trans('Subject', [], 'contactme'),
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'attr' => [
                    'id' => 'subject',
                    'class' => 'form-control',
                    'maxlength' => 255,
                    'placeholder' => $this->translator->trans('Subject', [], 'contactme'),
                    'aria-describedby' => 'invalidSubjectFeedback',
                ],
                'constraints' => [
                    new Length([
                        'max' => 255,
                    ]),
                ],
                'help' => $this->translator->trans('Please enter a subject', [], 'contactme'),
                'help_attr' => [
                    'class' => 'invalid-feedback',
                ],
            ])
            ->add('message', TextareaType::class, [
                'label' => $this->translator->trans('Message', [], 'contactme'),
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'attr' => [
                    'rows' => 10,
                    'id' => 'message',
                    'class' => 'form-control',
                    'placeholder' => $this->translator->trans('Message', [], 'contactme'),
                    'aria-describedby' => 'invalidMessageFeedback',
                ],
                'constraints' => [
                    new NotBlank(),
                ],
                'help' => $this->translator->trans('Please enter your message', [], 'contactme'),
                'help_attr' => [
                    'class' => 'invalid-feedback',
                ],
            ])->add('recaptcha', ReCaptchaType::class, [
                'mapped' => false,
                'required' => true,
                'label' => $this->translator->trans('Captcha'),
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'invalid_message' => $this->translator->trans('Captcha validation error.'),
                'constraints' => [
                    new ReCaptcha(),
                ],
                'help' => $this->translator->trans('Please confirm that you are not a robot.'),
                'help_attr' => [
                    'class' => 'invalid-feedback',
                ],
            ])->add('save', SubmitType::class, [
                'attr' => ['class' => 'btn btn-primary mt-4'],
                'label' => $this->translator->trans('Send'),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Message::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'message',
            'csrf_message' => $this->translator->trans('Invalid CSRF token.', [], 'security'),
        ]);
    }
}
