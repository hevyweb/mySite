<?php

namespace App\Form;

use App\Entity\Article;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class ArticleListType extends AbstractType
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dir', ChoiceType::class, [
                'choices' => [
                    Criteria::ASC,
                    Criteria::DESC,
                ],
                'mapped' => false,
                'required' => false,
            ])
            ->add('sorting', ChoiceType::class, [
                'choices' => [
                    'title',
                    'locale',
                    'draft',
                    'hit',
                    'createdAt',
                ],
                'mapped' => false,
                'required' => false,
            ])
            ->add('page', IntegerType::class, [
                'constraints' => [
                    new PositiveOrZero(),
                    new LessThanOrEqual(
                        $this->entityManager->getRepository(Article::class)->getCount($builder->getData())
                    ),
                ],
                'mapped' => false,
                'required' => false,
            ]);
    }
}
