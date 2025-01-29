<?php

namespace App\Controller;

use App\DTO\SearchArticle;
use App\Entity\Article;
use App\Entity\ArticleTranslation;
use App\Form\ArticleTranslationType;
use App\Form\ArticleType;
use App\Service\ArrayService;
use App\Service\FileSystem\FileManagementInterface;
use App\Traits\FlashMessageTrait;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @psalm-api
 */
class ArticleController extends AbstractController
{
    use LoggerAwareTrait;
    use FlashMessageTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
        private readonly FileManagementInterface $fileManager,
        private readonly ParameterBagInterface $parameterBag,
        private readonly ArrayService $arrayService,
    ) {
    }

    public function index(#[MapQueryString] ?SearchArticle $searchArticle = new SearchArticle()): Response
    {
        return $this->render('article/index.html.twig', [
            'articles' => $this->entityManager->getRepository(Article::class)->search($searchArticle),
            'title' => $this->translator->trans('Articles', [], 'article'),
            'currentFilters' => [
                'search' => $searchArticle->search,
                'sorting' => $searchArticle->sorting,
                'dir' => $searchArticle->dir,
            ],
            'lastPage' => ceil($this->entityManager->getRepository(Article::class)->getCount($searchArticle) / $searchArticle->limit),
            'currentPage' => $searchArticle->page,
            'tagFiltering' => $searchArticle->tag,
        ]);
    }

    public function create(Request $request): Response
    {
        $article = new Article();
        $translation = new ArticleTranslation();
        $translation->setLocale($request->getLocale());
        $article->addArticleTranslation($translation);

        $form = $this->buildForm($article, $translation);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->setImage($form, $translation);
                $translation
                    ->setCreatedBy($this->getUser())
                    ->setCreatedAt(new \DateTimeImmutable())
                    ->setUpdatedBy($this->getUser())
                    ->setUpdatedAt(new \DateTime());
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
            'submit' => $this->translator->trans('Save'),
            'article' => $article,
        ]);
    }

    public function translate(Request $request): Response
    {
        $articleTranslationId = $request->get('id');

        $articleTranslationRepository = $this->entityManager->getRepository(ArticleTranslation::class);

        $articleTranslation = $articleTranslationRepository->find($articleTranslationId);

        if (!$articleTranslation) {
            throw new NotFoundHttpException($this->translator->trans('Article translation with id {{ id }} not found.', ['id' => $articleTranslationId], 'article'));
        }

        $locale = $request->get('locale');

        if (!in_array($locale, $this->getParameter('app_locales'))) {
            throw new NotFoundHttpException($this->translator->trans('Locale {{ locale }} not found', ['locale' => $locale], 'languages'));
        }

        if ($existingArticle = $articleTranslationRepository->findOneBy([
            'article' => $articleTranslation->getArticle(),
            'locale' => $locale])
        ) {
            return $this->redirectToRoute('article-edit', ['id' => $existingArticle->getId()]);
        }

        $newArticleTranslation = clone $articleTranslation;
        $newArticleTranslation
            ->setLocale($locale)
            ->setCreatedBy($this->getUser())
            ->setUpdatedAt(new \DateTime())
            ->setUpdatedBy($this->getUser())
        ;
        try {
            $this->cloneImage($articleTranslation, $newArticleTranslation);
        } catch (FileException $e) {
            $this->logger->error($e->getMessage());
            $this->addFlash(self::ERROR, $this->translator->trans('Error occurred. Not able to copy the image.'));

            return $this->redirectToRoute('article-edit', [
                'slug' => $articleTranslation->getArticle()->getSlug(),
                'locale' => $articleTranslation->getLocale(),
            ]);
        }

        $this->entityManager->persist($newArticleTranslation);
        $this->entityManager->flush();

        return $this->redirectToRoute('article-edit', [
            'slug' => $newArticleTranslation->getArticle()->getSlug(),
            'locale' => $newArticleTranslation->getLocale(),
        ]);
    }

    public function update(Request $request): Response
    {
        $locale = $request->get('locale');
        $article = $this->entityManager->getRepository(Article::class)->findOneBySlug($request->get('slug'));
        if (!$article) {
            throw $this->createNotFoundException();
        }
        $translation = $article->getArticleTranslation($locale);

        if (!$translation) {
            $translation = $article->getArticleTranslationWithFallBack($locale);
            if (!$translation) {
                return $this->redirectToRoute('article-create');
            }
        }

        $form = $this->buildForm($article, $translation);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->setImage($form, $translation);
                $translation
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
                    $this->removeImage($article);
                    $this->entityManager->remove($article);
                }
                $this->entityManager->flush();
            }
        }

        return $this->redirectToRoute('article-list');
    }

    private function setImage(FormInterface $form, ArticleTranslation $articleTranslation): void
    {
        $file = $form->get('translation')->get('image')->getData();
        if ($file) {
            $file = $this->fileManager->save($file, $this->parameterBag->get('images_article'));
            if (!empty($articleTranslation->getImage())) {
                $this->fileManager->delete($articleTranslation->getImage(), $this->parameterBag->get('images_article'));
            }
            $articleTranslation->setImage($file);
        }
    }

    private function cloneImage(ArticleTranslation $articleTranslation, ArticleTranslation $newArticleTranslation): void
    {
        if (null != $articleTranslation->getImage()) {
            $destination = $this->parameterBag->get('images_article');
            $newImage = $this->fileManager->copy($articleTranslation->getImage(), $destination, $destination);
            $newArticleTranslation->setImage($newImage);
        }
    }

    private function removeImage(Article $article): void
    {
        foreach ($article->getArticleTranslations() as $articleTranslation) {
            try {
                if ($articleTranslation->getImage()) {
                    $this->fileManager->delete($articleTranslation->getImage(), $this->parameterBag->get('images_article'));
                }
            } catch (FileNotFoundException $exception) {
                $this->logger->error($exception->getMessage());
                $this->addFlash(self::SUCCESS, $this->translator->trans('Can not remove image of the article.', [], 'article'));
            }
        }
    }

    private function buildForm(Article $article, ArticleTranslation $articleTranslation): FormInterface
    {
        return $this->createFormBuilder([
            'article' => $article,
            'translation' => $articleTranslation,
        ])
            ->add('article', ArticleType::class)
            ->add('translation', ArticleTranslationType::class)
            ->getForm();
    }
}
