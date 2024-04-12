<?php

namespace App\Form;

use App\DataTransformer\TagDataTransformer;
use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @psalm-suppress MissingTemplateParam
 */
class ArticleType extends AbstractType
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly TagDataTransformer $dataTransformer,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('tags', TextType::class, [
                'label' => $this->translator->trans('Tags', [], 'article'),
                'attr' => [
                    'maxlength' => 255,
                    'class' => 'form-control tag-input',
                ],
            ])
            ->add('slug', TextType::class, [
                'label' => $this->translator->trans('Slug', [], 'article'),
                'attr' => [
                    'maxlength' => 255,
                    'class' => 'form-control slug-input',
                ],
                'constraints' => [
                    new Length([
                        'max' => 255,
                    ]),
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
