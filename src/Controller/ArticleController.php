<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleListType;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use App\Service\ArrayService;
use App\Service\File;
use App\Traits\FlashMessageTrait;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class ArticleController extends AbstractController
{
    use LoggerAwareTrait;
    use FlashMessageTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
        private readonly File $fileService,
        private readonly ParameterBagInterface $parameterBag,
        private readonly ArrayService $arrayService,
    ) {
    }

    public function index(Request $request): Response
    {
        $abstractForm = $this->createForm(ArticleListType::class, $request);
        $abstractForm->handleRequest($request);
        if ($abstractForm->isSubmitted() && !$abstractForm->isValid()) {
            return $this->redirectToRoute('article-list');
        }
        /**
         * @var ArticleRepository $repository
         */
        $repository = $this->entityManager->getRepository(Article::class);

        return $this->render('article/index.html.twig', [
            'articles' => $repository->search($request),
            'title' => $this->translator->trans('Articles', [], 'article'),
            'currentFilters' => [
                'search' => $request->get('search'),
                'sorting' => $request->get('sorting', 'createdAt'),
                'dir' => $request->get('dir', Criteria::DESC),
            ],
            'lastPage' => ceil($repository->getCount($request) / $repository::PER_PAGE),
            'currentPage' => (int) $request->get('page', 1),
        ]);
    }

    public function create(Request $request): Response
    {
        $article = new Article();

        $article->setLocale($request->getLocale());
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $file = $form->get('image')->getData();
                if ($file) {
                    $file = $this->fileService->saveFileTo($file, $this->parameterBag->get('images_article'));
                    $article->setImage($file->getFilename());
                }
                $article
                    ->setCreatedBy($this->getUser())
                    ->setCreatedAt(new \DateTimeImmutable());
                $this->entityManager->persist($article);
                $this->entityManager->flush();

                return $this->redirectToRoute('article-list');
            } catch (FileException $fileException) {
                $error = new FormError($fileException->getMessage());
                $form->get('image')->addError($error);
            }
        }

        return $this->render('article/create.html.twig', [
            'title' => $this->translator->trans('Create an article', [], 'article'),
            'form' => $form->createView(),
            'translations' => [],
            'submit' => $this->translator->trans('Save'),
        ]);
    }

    public function translate(Request $request): Response
    {
        $originArticleId = $request->get('id');

        $articleRepository = $this->entityManager->getRepository(Article::class);

        $originArticle = $articleRepository->find($originArticleId);

        if (!$originArticle) {
            throw new NotFoundHttpException($this->translator->trans('Article with id {{ id }} not found.', ['id' => $originArticleId], 'article'));
        }

        $locale = $request->get('locale');

        if (!in_array($locale, $this->getParameter('app_locales'))) {
            throw new NotFoundHttpException($this->translator->trans('Locale {{ locale }} not found', ['locale' => $locale], 'languages'));
        }

        if ($existingArticle = $articleRepository->findOneBy(['slug' => $originArticle->getSlug(), 'locale' => $locale])) {
            return $this->redirectToRoute('article-edit', ['id' => $existingArticle->getId()]);
        }

        $article = new Article();
        $article->setLocale($locale)
            ->setImage($originArticle->getImage())
            ->setCreatedAt(new \DateTimeImmutable())
            ->setBody($originArticle->getBody())
            ->setDraft(true)
            ->setPreview($originArticle->getPreview())
            ->setSlug($originArticle->getSlug())
            ->setTags($originArticle->getTags())
            ->setTitle($originArticle->getTitle())
            ->setCreatedBy($this->getUser());

        $this->entityManager->persist($article);
        $this->entityManager->flush();

        return $this->redirectToRoute('article-edit', ['id' => $article->getId()]);
    }

    public function update(Request $request): Response
    {
        $article = $this->entityManager->getRepository(Article::class)->find((int) $request->get('id'));
        if (!$article) {
            throw $this->createNotFoundException();
        }
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $file = $form->get('image')->getData();
                if ($file) {
                    $file = $this->fileService->saveFileTo($file, $this->parameterBag->get('images_article'));
                    if (!empty($article->getImage())) {
                        $this->fileService->remove($article->getImage(), $this->parameterBag->get('images_article'));
                    }
                    $article->setImage($file->getFilename());
                }
                $article
                    ->setUpdatedBy($this->getUser())
                    ->setUpdatedAt(new \DateTime());
                $this->entityManager->flush();

                return $this->redirectToRoute('article-list');
            } catch (FileException $fileException) {
                $error = new FormError($fileException->getMessage());
                $form->get('image')->addError($error);
            }
        }

        return $this->render('article/create.html.twig', [
            'title' => $this->translator->trans('Update an article', [], 'article'),
            'article' => $article,
            'form' => $form->createView(),
            'translations' => $this->entityManager->getRepository(Article::class)->findBy(['slug' => $article->getSlug()]),
            'submit' => $this->translator->trans('Update'),
        ]);
    }

    public function delete(Request $request): Response
    {
        $ids = $this->arrayService->getIntegerIds($request->get('id'));
        if (count($ids)) {
            $articles = $this->entityManager->getRepository(Article::class)->findBy(['id' => $ids]);
            if (count($articles)) {
                foreach ($articles as $article) {
                    try {
                        if ($article->getImage()) {
                            $this->fileService->remove($article->getImage(), $this->parameterBag->get('images_article'));
                        }
                        $this->entityManager->remove($article);
                        $this->logger->debug('Article "'.$article->getTitle().'" removed.');
                    } catch (FileNotFoundException $exception) {
                        $this->logger->error($exception->getMessage());
                        $this->addFlash(self::$success, $this->translator->trans('Can not remove image of the article.', [], 'article'));

                        return $this->redirectToRoute('article-list');
                    }
                }
                $this->entityManager->flush();
            }
        }

        return $this->redirectToRoute('article-list');
    }
}
