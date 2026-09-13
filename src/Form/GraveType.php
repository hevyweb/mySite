<?php

namespace App\Form;

use App\Entity\Grave;
use App\Type\Gender;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @psalm-suppress MissingTemplateParam
 */
class GraveType extends AbstractType
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Grave $grave */
        $grave = $builder->getData();

        $builder
            ->add('firstName', TextType::class, [
                'label' => $this->translator->trans('First Name', [], 'necropolis'),
                'attr' => [
                    'maxlength' => 255,
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 2,
                        'max' => 255,
                    ]),
                ],
                'label_attr' => [
                    'class' => 'fw-bold',
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => $this->translator->trans('Last Name', [], 'necropolis'),
                'attr' => [
                    'maxlength' => 255,
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 2,
                        'max' => 255,
                    ]),
                ],
                'label_attr' => [
                    'class' => 'fw-bold',
                ],
            ])
            ->add('maidenName', TextType::class, [
                'label' => $this->translator->trans('Maiden Name', [], 'necropolis'),
                'required' => false,
                'attr' => [
                    'maxlength' => 255,
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new Length([
                        'max' => 255,
                    ]),
                ],
                'label_attr' => [
                    'class' => 'fw-bold',
                ],
            ])
            ->add('gender', ChoiceType::class, [
                'label' => $this->translator->trans('Gender', [], 'necropolis'),
                'choices' => [
                    $this->translator->trans('Male', [], 'user') => Gender::MALE,
                    $this->translator->trans('Female', [], 'user') => Gender::FEMALE,
                ],
                'attr' => [
                    'class' => 'form-select', // Bootstrap 5 select styling
                ],
                'constraints' => [
                    new NotBlank(),
                ],
                'label_attr' => [
                    'class' => 'fw-bold',
                ],
            ])
            ->add('birthDate', DateType::class, [
                'label' => $this->translator->trans('Birth Date', [], 'necropolis'),
                'html5' => true,
                'required' => false,
                'attr' => [
                    'class' => 'js-datepicker form-control',
                ],
                'label_attr' => [
                    'class' => 'fw-bold',
                ],
            ])
            ->add('deathDate', DateType::class, [
                'label' => $this->translator->trans('Death Date', [], 'necropolis'),
                'html5' => true,
                'attr' => [
                    'class' => 'js-datepicker form-control',
                ],
                'constraints' => [
                    new NotBlank(),
                    new Callback([$this, 'compareDates']),
                ],
                'label_attr' => [
                    'class' => 'fw-bold',
                ],
            ])
            ->add('birthPlace', TextType::class, [
                'label' => $this->translator->trans('Birth Place', [], 'necropolis'),
                'required' => false,
                'attr' => [
                    'maxlength' => 255,
                    'class' => 'form-control',
                ],
                'label_attr' => [
                    'class' => 'fw-bold',
                ],
            ])
            ->add('birthPlaceCoordinates', TextType::class, [
                'label' => $this->translator->trans('Birth Place Coordinates', [], 'necropolis'),
                'required' => false,
                'attr' => [
                    'maxlength' => 515,
                    'class' => 'form-control',
                ],
                'label_attr' => [
                    'class' => 'fw-bold',
                ],
            ])
            ->add('deathPlace', TextType::class, [
                'label' => $this->translator->trans('Death Place', [], 'necropolis'),
                'required' => false,
                'attr' => [
                    'maxlength' => 255,
                    'class' => 'form-control',
                ],
            ])
            ->add('deathPlaceCoordinates', TextType::class, [
                'label' => $this->translator->trans('Death Place Coordinates', [], 'necropolis'),
                'required' => false,
                'attr' => [
                    'maxlength' => 515,
                    'class' => 'form-control',
                ],
                'label_attr' => [
                    'class' => 'fw-bold',
                ],
            ])
            ->add('gravePlace', TextType::class, [
                'label' => $this->translator->trans('Grave Location', [], 'necropolis'),
                'required' => false,
                'attr' => [
                    'maxlength' => 255,
                    'class' => 'form-control',
                ],
                'label_attr' => [
                    'class' => 'fw-bold',
                ],
            ])
            ->add('gravePlaceCoordinates', TextType::class, [
                'label' => $this->translator->trans('Grave Coordinates', [], 'necropolis'),
                'required' => false,
                'attr' => [
                    'maxlength' => 515,
                    'class' => 'form-control',
                ],
                'label_attr' => [
                    'class' => 'fw-bold',
                ],
            ])
            ->add('epitaph', TextType::class, [
                'label' => $this->translator->trans('Epitaph', [], 'necropolis'),
                'required' => false,
                'attr' => [
                    'maxlength' => 255,
                    'class' => 'form-control',
                ],
                'label_attr' => [
                    'class' => 'fw-bold',
                ],
            ])
            ->add('biography', TextareaType::class, [
                'label' => $this->translator->trans('Biography', [], 'necropolis'),
                'required' => false,
                'attr' => [
                    'rows' => 10,
                    'class' => 'html-editor',
                ],
                'label_attr' => [
                    'class' => 'fw-bold',
                ],
            ])
            ->add('image', FileType::class, [
                'label' => $this->translator->trans('Image', [], 'necropolis'),
                'mapped' => false,
                'required' => !$grave || !$grave->getImage(),
                'constraints' => [
                    new File([
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/jpg',
                            'image/png',
                            'image/gif',
                            'image/bmp',
                        ],
                        'mimeTypesMessage' => $this->translator->trans('Please upload valid image. Support formats jpg, jpeg, png, gif, bmp', [], 'necropolis'),
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                ],
                'label_attr' => [
                    'class' => 'fw-bold',
                ],
            ])
            ->add('isPublished', CheckboxType::class, [
                'label' => $this->translator->trans('Published', [], 'necropolis'),
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input',
                ],
                'label_attr' => [
                    'class' => 'fw-bold',
                ],
            ])
        ;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Grave::class,
        ]);
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod This is used as a validation callback.
     */
    public function compareDates(?\DateTime $deathDate, ExecutionContext $executionContext): bool
    {
        if (!empty($deathDate)) {
            /** @var Form $form */
            $form = $executionContext->getRoot();
            /** @var \DateTime|null $birthDate */
            $birthDate = $form->get('birthDate')->getData();

            if ($birthDate && $birthDate > $deathDate) {
                $executionContext->addViolation(
                    $this->translator->trans('Date of death cannot be earlier than the date of birth.', [], 'necropolis')
                );

                return false;
            }
        }

        return true;
    }
}