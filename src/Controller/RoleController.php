<?php

namespace App\Controller;

use App\Entity\Role;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @psalm-api
 */
class RoleController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function index(): Response
    {
        return $this->render('role/index.html.twig', [
            'roles' => $this->entityManager->getRepository(Role::class)->findAll(),
            'title' => $this->translator->trans('Roles', [], 'role'),
        ]);
    }
}
