<?php

namespace App\Controller;

use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class DashboardController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator
    ) {
    }

    public function index(): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'title' => $this->translator->trans('Admin panel', [], 'dashboard'),
            'messages' => $this->entityManager->getRepository(Message::class)->countNew(),
        ]);
    }
}
