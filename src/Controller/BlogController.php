<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\ArticleTranslation;
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
    public const PER_PAGE = 6;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
    ) {
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
            'lastPage' => ceil($count / self::PER_PAGE),
            'currentPage' => $page,
        ]);
    }

    public function view(Request $request): Response
    {
        /**
         * @var Article|null      $article
         * @var ArticleRepository $articleRepository
         */
        $articleRepository = $this->entityManager->getRepository(Article::class);
        $article = $articleRepository->findOneBySlug($request->get('slug'));

        if (empty($article)) {
            throw new NotFoundHttpException($this->translator->trans('Article not found.', [], 'article'));
        }

        $translation = $article->getArticleTranslationWithFallBack($request->getLocale());

        return $this->render('blog/view.html.twig', [
            'title' => $translation->getTitle(),
            'article' => $article,
            'translation' => $translation,
        ], $this->incrementHit($translation)
        );
    }

    private function incrementHit(ArticleTranslation $articleTranslation): Response
    {
        $hit = $_COOKIE['hit'] ?? '';

        if ('' === $hit) {
            $hit = [];
        } else {
            $hit = array_unique(array_map('intval', explode(',', $hit)));
        }
        $response = new Response();
        if (!in_array($articleTranslation->getId(), $hit)) {
            $articleTranslation->setHit($articleTranslation->getHit() + 1);
            $hit[] = $articleTranslation->getId();
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
