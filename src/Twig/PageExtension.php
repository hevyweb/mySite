<?php

namespace App\Twig;

use App\Entity\Page;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @template TValue
 */
class PageExtension extends AbstractExtension
{
    public function __construct(
        readonly private EntityManagerInterface $entityManager,
    ) {
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

    /**
     * @param array<string, TValue> $context
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getPageContent(Environment $environment, array $context): string
    {
        /**
         * @var Request $request
         */
        $request = $context['app']->getRequest();
        $locale = $request->getLocale();
        $route = $request->attributes->get('_route');

        $pages = $this->entityManager->getRepository(Page::class)->findBy(['route' => $route, 'locale' => $locale]);

        return $environment->render('common/page.html.twig', [
            'pages' => $pages,
        ]);
    }
}
