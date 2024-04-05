<?php

namespace App\Form;

use App\Entity\Page;
use App\Service\Language;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends AbstractType<string>
 */
class PageType extends AbstractType
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly RouterInterface $router,
        private readonly Language $language,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => $this->translator->trans('Title'),
                'attr' => [
                    'maxlength' => 255,
                    'id' => 'name',
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new Length([
                        'max' => 255,
                    ]),
                    new NotBlank(),
                ],
            ])
            ->add('body', TextareaType::class, [
                'attr' => [
                    'rows' => 10,
                    'id' => 'body',
                    'class' => 'html-editor',
                ],
                'label' => $this->translator->trans('Description'),
            ])
            ->add('locale', ChoiceType::class, [
                'choices' => $this->language->buildLanguages(),
                'label' => $this->translator->trans('Locale'),
                'attr' => [
                    'maxlength' => 255,
                    'id' => 'locale',
                    'class' => 'form-control',
                ],
            ])
            ->add('route', ChoiceType::class, [
                'choices' => $this->buildRouteList(),
                'label' => $this->translator->trans('Route'),
                'attr' => [
                    'maxlength' => 255,
                    'id' => 'route',
                    'class' => 'form-control',
                ],
            ])
            ->add('save', SubmitType::class, [
                'attr' => ['class' => 'btn btn-primary mt-4'],
                'label' => $this->translator->trans('Save'),
            ])
        ;
    }

    public function buildRouteList(): array
    {
        $routes = array_keys($this->router->getRouteCollection()->all());
        $list = [];
        foreach ($routes as $route) {
            if (preg_match('/^_/', $route)) {
                // ignore service routes
                continue;
            }
            if (preg_match('/^([^\-]+)-(.+)/', $route, $matches)) {
                $list[$matches[1]][$matches[2]] = $route;
            } else {
                $list[$route][$route] = $route;
            }
        }

        return $list;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Page::class,
        ]);
    }
}
