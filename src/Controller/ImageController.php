<?php

namespace App\Controller;

use App\Service\FileSystem\FileManagementInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @psalm-api
 */
class ImageController extends AbstractController
{
    public function __construct(
        private readonly FileManagementInterface $fileManagement,
    ) {
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
            $newName = basename($this->fileManagement->save($request->files, $this->getParameter('images_article')));

            return $this->json([
                'location' => $request->getSchemeAndHttpHost().'/blog/'.$newName,
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
