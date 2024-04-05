<?php

namespace App\Controller;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @psalm-api
 */
class HomeController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function index(Request $request): Response
    {
        return $this->render('home/index.html.twig', [
            'title' => $this->translator->trans('Dmytro Dzyuba | personal web site'),
            'articles' => $this->em->getRepository(Article::class)->getTopArticles(2, $request->getLocale()),
        ]);
    }
}
