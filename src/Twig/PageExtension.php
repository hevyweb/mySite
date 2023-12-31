<?php

namespace App\Twig;

use App\Entity\Page;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PageExtension extends AbstractExtension
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    )
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('page_content', [$this, 'getPageContent'], [
                'needs_context' => true,
                'needs_environment' => true,
            ]),
        ];
    }

    public function getPageContent(Environment $environment, array $context)
    {
        /**
         * @var Request $request
         */
        $request = $context['app']->getRequest();
        $locale = $request->getLocale();
        $route = $request->attributes->get('_route');

        $pages = $this->entityManager->getRepository(Page::class)->findBy(['route' => $route, 'locale' => $locale]);
        return $environment->render('common/page.html.twig', [
            'pages' => $pages
        ]);
    }
}
