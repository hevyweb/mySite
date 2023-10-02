<?php

namespace App\Form;

use App\DataTransformer\TagDataTransformer;
use App\Entity\Article;
use App\Traits\LocaleBuilderTrait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Contracts\Translation\TranslatorInterface;

class ArticleType extends AbstractType
{
    use LocaleBuilderTrait;

    public function __construct(
        private readonly TranslatorInterface $translator,
        private TagDataTransformer $dataTransformer,
    )
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => $this->translator->trans('Title', [], 'article'),
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
                'label' => $this->translator->trans('Locale', [], 'article'),
            ])
            ->add('body', TextareaType::class, [
                'attr' => [
                    'rows' => 10
                ],
                'label' => $this->translator->trans('Body', [], 'article'),
                ]
            )
            ->add('preview', TextareaType::class, [
                'attr' => [
                    'rows' => 10
                ],
                'label' => $this->translator->trans('Preview', [], 'article'),
                ]
            )
            ->add('tags', TextType::class, [
                'label' => $this->translator->trans('Tags', [], 'article'),
                'attr' => [
                    'maxlength' => 255,
                ],
            ])
            ->add('slug', TextType::class, [
                'label' => $this->translator->trans('Slug', [], 'article'),
                'attr' => [
                    'maxlength' => 255,
                ],
                'constraints' => [
                    new Length([
                        'max' => 255,
                    ]),

                ],
            ])
            ->add('draft', CheckboxType::class, [
                'label' => $this->translator->trans('Draft', [], 'article'),
                'required' => false,
            ])
            ->add('image', FileType::class, [
                'label' => $this->translator->trans('Image', [], 'article'),
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

        $builder->get('tags')->addModelTransformer($this->dataTransformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
