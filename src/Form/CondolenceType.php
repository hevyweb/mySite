<?php

namespace App\Form;

use App\Entity\Condolence;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class CondolenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description', TextareaType::class, [
                'label' => 'Condolence Message',
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Write your message here...',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a message.',
                    ]),
                ],
            ])
            ->add('name', TextType::class, [
                'label' => 'Your Name',
                'required' => false,
            ])
            ->add('email', EmailType::class, [
                'label' => 'Your Email',
                'required' => false,
                'constraints' => [
                    new Email([
                        'message' => 'Please enter a valid email address.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Condolence::class,
        ]);
    }
}