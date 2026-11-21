<?php

namespace App\Controller;

use App\Entity\Page;
use App\Form\PageType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @psalm-api
 */
class PageController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function index(): Response
    {
        $pages = $this->entityManager->getRepository(Page::class)->findAll();

        return $this->render('page/index.html.twig', [
            'pages' => $pages,
            'title' => $this->translator->trans('Page content', [], 'page'),
        ]);
    }

    public function create(Request $request): Response
    {
        $page = new Page();

        $form = $this->createForm(PageType::class, $page);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $page
                ->setCreatedAt(new \DateTimeImmutable())
                ->setCreatedBy($this->getUserEntity());
            $this->entityManager->persist($page);
            $this->entityManager->flush();

            return $this->redirectToRoute('page-list');
        }

        return $this->render('page/form.html.twig', [
            'form' => $form->createView(),
            'title' => $this->translator->trans('Create page Content', [], 'page'),
        ]);
    }

    public function update(Request $request): Response
    {
        $page = $this->entityManager->getRepository(Page::class)->find(
            (int) $request->get('id')
        );

        if (empty($page)) {
            throw new NotFoundHttpException('Content for page "'.$request->get('id').'" not found.');
        }

        $form = $this->createForm(PageType::class, $page);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $page
                ->setUpdatedAt(new \DateTime())
                ->setUpdatedBy($this->getUserEntity());
            $this->entityManager->persist($page);
            $this->entityManager->flush();

            return $this->redirectToRoute('page-list');
        }

        return $this->render('page/form.html.twig', [
            'form' => $form->createView(),
            'title' => $this->translator->trans('Update page Content', [], 'page'),
            'submit' => $this->translator->trans('Create'),
        ]);
    }

    public function delete(Request $request): Response
    {
        $ids = $request->get('id');
        if (is_array($ids)) {
            $ids = array_unique(array_map('intval', array_keys($ids)));
            $pages = $this->entityManager->getRepository(Page::class)->findBy(['id' => $ids]);
            if (count($pages)) {
                foreach ($pages as $page) {
                    $this->entityManager->remove($page);
                }
                $this->entityManager->flush();
            }
        }

        return $this->redirectToRoute('page-list');
    }
}
