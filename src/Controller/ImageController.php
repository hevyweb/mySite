<?php

namespace App\Controller;

use App\Service\FileSystem\FileManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @psalm-api
 */
class ImageController extends AbstractController
{
    public function __construct(
        private readonly FileManagerInterface $fileManagement,
    ) {
    }

    public function index(): Response
    {
        return $this->redirectToRoute('home');
    }

    public function upload(Request $request): Response
    {
        try {
            /** @var UploadedFile|null $file */
            $file = $request->files->get('file');
            if (null === $file) {
                throw new \Exception('No file uploaded.');
            }

            $newName = basename($this->fileManagement->save($file, $this->getParameter('images_article')));

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
