<?php

namespace App\Form;

use App\Entity\Experience;
use App\Traits\LocaleBuilderTrait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Contracts\Translation\TranslatorInterface;

class ExperienceType extends AbstractType
{
    use LocaleBuilderTrait;

    const START_YEAR = 2008;

    public function __construct(
        private readonly TranslatorInterface $translator,
        private string $defaultDateFormat,
    )
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => $this->translator->trans('Title', [], 'experience'),
                'attr' => [
                    'maxlength' => 255,
                ],
                'constraints' => [
                    new Length([
                        'max' => 255,
                    ]),
                ],
            ])
            ->add('locale', ChoiceType::class, [
                'choices' => $this->buildLanguages(),
                'label' => $this->translator->trans('Locale'),
            ])
            ->add('description', TextareaType::class, [
                'attr' => [
                    'rows' => 10
                ],
                'label' => $this->translator->trans('Description'),
                ]
            )
            ->add('fromDate', DateType::class, [
                'label' => $this->translator->trans('From', [], 'experience'),
                'format' => $this->defaultDateFormat,
                'years' => $this->getYears(),
            ])
            ->add('toDate', DateType::class, [
                'label' => $this->translator->trans('To', [], 'experience'),
                'format' => $this->defaultDateFormat,
                'years' => $this->getYears(),
            ])
            ->add('company', TextType::class, [
                'label' => $this->translator->trans('Company', [], 'experience'),
                'attr' => [
                    'maxlength' => 64,
                ],
                'constraints' => [
                    new Length([
                        'max' => 64,
                    ]),
                ],
            ])
            ->add('location', TextType::class, [
                'label' => $this->translator->trans('Location'),
                'attr' => [
                    'maxlength' => 64,
                ],
                'constraints' => [
                    new Length([
                        'max' => 64,
                    ]),
                ],
            ])
            ->add('image', FileType::class, [
                'label' => $this->translator->trans('Image'),
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/jpg',
                            'image/png',
                            'image/gif',
                            'image/bmp',
                        ],
                        'mimeTypesMessage' => $this->translator->trans('Please upload valid image. Support formats jpg, jpeg, png, gif, bmp'),
                    ])
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Experience::class,
        ]);
    }

    public function getYears(): array
    {
        $years = [];
        for ($n = self::START_YEAR; $n <= date('Y'); $n++){
            $years[$n] = $n;
        }
        return $years;
    }
}
