<?php

// src/Form/PersonPhotoType.php
namespace App\Form;

use App\Entity\PersonPhoto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Contracts\Translation\TranslatorInterface;

class GravePhotoType extends AbstractType
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {}
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('filename', FileType::class, [
                'label' => $this->translator->trans('Photo Image', [], 'necropolis'),
                'required' => true,
                'constraints' => [
                    new Image([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image (JPEG, PNG, WebP).',
                    ])
                ],
            ])
            ->add('description', TextType::class, [
                'label' => $this->translator->trans('Short Description', [], 'necropolis'),
                'required' => false,
                'attr' => [
                    'placeholder' => 'e.g., Family gathered at the location...',
                    'maxlength' => 255
                ]
            ])
            ->add('shootingDate', DateType::class, [
                'label' => $this->translator->trans('Shooting Date', [], 'necropolis'),
                'required' => false,
                'widget' => 'single_text',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PersonPhoto::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'necropolis_photo_upload',
        ]);
    }
}