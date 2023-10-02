<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class BlogController extends AbstractController
{
    const PER_PAGE = 6;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private TranslatorInterface $translator,
    )
    {

    }

    public function index(Request $request): Response
    {
        $page = (int) $request->get('page', 1);

        if ($page < 1) {
            $page = 1;
        }

        /**
         * @var ArticleRepository $articleRepository
         */
        $articleRepository = $this->entityManager->getRepository(Article::class);

        $count = $articleRepository->countBlogArticles($request->getLocale());

        return $this->render('blog/index.html.twig', [
            'title' => $this->translator->trans('Blog', [], 'article'),
            'articles' => $articleRepository->getBlogArticles(self::PER_PAGE, $page, $request->getLocale()),
            'lastPage' => ceil($count/self::PER_PAGE),
            'currentPage' => $page,
        ]);
    }

    public function view(Request $request): Response
    {
        /**
         * @var Article|null $article
         */
        $article = $this->entityManager->getRepository(Article::class)
            ->findArticleWithSubstitutes(
                $request->get('slug'),
                $request->getLocale(),
            );

        if (empty($article)) {
            throw new NotFoundHttpException($this->translator->trans('Article not found.', [], 'article'));
        }

        $response = $this->render('blog/view.html.twig', [
            'title' => $article->getTitle(),
            'article' => $article,
        ]);

        $hit = $_COOKIE['hit'] ?? '';

        if ($hit === '') {
            $hit = [];
        } else {
            $hit = array_unique(array_map('intval', explode(',', $hit)));
        }

        if (!in_array($article->getId(), $hit)) {
            $article->setHit($article->getHit() + 1);
            $hit[] = $article->getId();
            $cookie = Cookie::create('hit')
                ->withValue(implode(',', $hit))
                ->withExpires(new \DateTime('+24 hours'))
                ->withSecure();
            $response->headers->setCookie($cookie);
            $this->entityManager->flush();
        }

        return $response;
    }
}
