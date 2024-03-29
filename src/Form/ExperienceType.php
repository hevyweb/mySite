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
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Contracts\Translation\TranslatorInterface;

class ExperienceType extends AbstractType
{
    use LocaleBuilderTrait;

    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $experience = $builder->getData();
        $builder
            ->add('name', TextType::class, [
                'label' => $this->translator->trans('Title', [], 'experience'),
                'attr' => [
                    'maxlength' => 255,
                    'class' => 'form-control',
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
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('description', TextareaType::class, [
                'attr' => [
                    'rows' => 10,
                    'class' => 'form-control',
                ],
                'label' => $this->translator->trans('Description'),
            ]
            )
            ->add('fromDate', DateType::class, [
                'label' => $this->translator->trans('From', [], 'experience'),
                'html5' => true,
                'attr' => [
                    'data-provide' => 'datepicker',
                    'class' => 'js-datepicker  form-control',
                ],
            ])
            ->add('toDate', DateType::class, [
                'label' => $this->translator->trans('To', [], 'experience'),
                'html5' => true,
                'required' => false,
                'attr' => [
                    'class' => 'js-datepicker form-control',
                ],
                'constraints' => [
                    new Callback([$this, 'compareDates']),
                ],
            ])
            ->add('company', TextType::class, [
                'label' => $this->translator->trans('Company', [], 'experience'),
                'attr' => [
                    'maxlength' => 64,
                    'class' => 'form-control',
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
                    'class' => 'form-control',
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
                'required' => !$experience->getImage(),
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
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
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

    public function compareDates(?\DateTime $toDate, ExecutionContext $executionContext): bool
    {
        if (!empty($toDate)) {
            /**
             * @var Form $form
             */
            $form = $executionContext->getRoot();
            $fromDate = $form->get('fromDate')->getData();

            if ($fromDate > $toDate) {
                $executionContext->addViolation($this->translator->trans('Starting date is greater then end date.', [], 'experience'));

                return false;
            }
        }

        return true;
    }
}
