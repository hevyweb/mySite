<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class DashboardController extends AbstractController
{
    public function __construct(
        private TranslatorInterface $translator
    )
    {
    }

    public function index(): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'title' => $this->translator->trans('Admin panel', [], 'dashboard'),
        ]);
    }
}
