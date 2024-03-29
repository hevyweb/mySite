<?php

namespace App\Controller;

use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TagController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function index(): Response
    {
        return $this->render('tag/index.html.twig', [
            'controller_name' => 'TagController',
        ]);
    }

    public function search(Request $request): Response
    {
        $name = $request->get('name');
        $tags = $this->entityManager->getRepository(Tag::class)->search($name);

        return $this->json($tags, Response::HTTP_OK, [], ['groups' => 'search']);
    }
}
