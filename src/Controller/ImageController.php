<?php

namespace App\Controller;

use App\Service\ImageUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ImageController extends AbstractController
{
    public function __construct(
        private ImageUploader $imageUploader,
    )
    {
    }

    public function index(): Response
    {
        return $this->render('image/index.html.twig', [
            'controller_name' => 'ImageController',
        ]);
    }

    public function upload(Request $request): Response
    {
        try {
            $newName = basename($this->imageUploader->save($request->files, $this->getParameter('images_article')));

            return $this->json([
                'location' => '/blog/' . $newName
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
