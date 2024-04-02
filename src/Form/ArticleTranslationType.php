<?php

namespace App\Form;

use App\Entity\ArticleTranslation;
use App\Traits\LocaleBuilderTrait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Contracts\Translation\TranslatorInterface;

class ArticleTranslationType extends AbstractType
{
    use LocaleBuilderTrait;

    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => $this->translator->trans('Title', [], 'article'),
                'attr' => [
                    'maxlength' => 255,
                    'class' => 'form-control slug-source',
                ],
                'constraints' => [
                    new Length([
                        'max' => 255,
                    ]),
                ],
            ])
            ->add('body', TextareaType::class, [
                'attr' => [
                    'rows' => 10,
                    'class' => 'html-editor',
                ],
                'label' => $this->translator->trans('Body', [], 'article'),
            ])
            ->add('preview', TextareaType::class, [
                'attr' => [
                    'rows' => 10,
                    'class' => 'form-control',
                ],
                'label' => $this->translator->trans('Preview', [], 'article'),
            ])
            ->add('draft', CheckboxType::class, [
                'label' => $this->translator->trans('Translation enabled', [], 'article'),
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input',
                    'role' => 'switch',
                ],
                'label_attr' => [
                    'class' => 'form-check-label',
                ],
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
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, \Closure::fromCallable([$this, 'buildLocale']));
    }

    private function buildLocale(FormEvent $event): void
    {
        $event->getForm()
            ->add('locale', ChoiceType::class, [
                'choices' => $this->getAvailableLocales($event->getData()),
                'label' => $this->translator->trans('Locale', [], 'article'),
                'attr' => [
                    'class' => 'form-select',
                ],
            ]);
    }

    public function getAvailableLocales(ArticleTranslation $articleTranslation): array
    {
        $availableLocales = array_flip($this->buildLanguages());
        $translations = $articleTranslation->getArticle()->getArticleTranslations();
        foreach ($translations as $translation) {
            if ($translation->getId() != $articleTranslation->getId() && isset($availableLocales[$translation->getLocale()])) {
                unset($availableLocales[$translation->getLocale()]);
            }
        }

        return array_flip($availableLocales);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ArticleTranslation::class,
        ]);
    }
}
