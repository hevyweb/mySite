<?php

namespace App\Controller;

use App\Entity\Page;
use App\Form\PageType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

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
                ->setCreatedBy($this->getUser());
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
                ->setUpdatedBy($this->getUser());
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

    public function delete(): Response
    {
        return $this->redirectToRoute('page-list');
    }
}
