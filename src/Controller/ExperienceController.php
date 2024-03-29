<?php

namespace App\Controller;

use App\Entity\Experience;
use App\Form\ExperienceType;
use App\Service\ArrayService;
use App\Service\File;
use App\Traits\FlashMessageTrait;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class ExperienceController extends AbstractController
{
    use FlashMessageTrait;
    use LoggerAwareTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
        private readonly ParameterBagInterface $parameterBag,
        private readonly File $fileService,
        private readonly ArrayService $arrayService,
    ) {
    }

    public function index(): Response
    {
        $experience = $this->entityManager->getRepository(Experience::class)->findAll();

        return $this->render('experience/table.html.twig', [
            'experiences' => $experience,
            'title' => $this->translator->trans('Experiences', [], 'experiences'),
        ]);
    }

    public function create(Request $request): Response
    {
        $experience = new Experience();
        $form = $this->createForm(ExperienceType::class, $experience);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $file = $form->get('image')->getData();
                if ($file) {
                    $file = $this->fileService->saveFileTo($file, $this->parameterBag->get('images_experience'));
                    $experience->setImage($file->getFilename());
                }
                $this->entityManager->persist($experience);
                $this->entityManager->flush();

                return $this->redirectToRoute('experience-list');
            } catch (FileException $fileException) {
                $error = new FormError($fileException->getMessage());
                $form->get('image')->addError($error);
            }
        }

        return $this->render('experience/create.html.twig', [
            'title' => $this->translator->trans('Add working place', [], 'experience'),
            'form' => $form->createView(),
            'submit' => $this->translator->trans('Create'),
        ]);
    }

    public function update(Request $request): Response
    {
        $experience = $this->entityManager->getRepository(Experience::class)->find((int) $request->get('id'));
        $form = $this->createForm(ExperienceType::class, $experience);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $file = $form->get('image')->getData();
                if ($file) {
                    $file = $this->fileService->saveFileTo($file, $this->parameterBag->get('images_experience'));
                    if ($experience->getImage()) {
                        $this->fileService->remove($experience->getImage(), $this->parameterBag->get('images_experience'));
                    }
                    $experience->setImage($file->getFilename());
                }
                $this->entityManager->flush();

                return $this->redirectToRoute('experience-list');
            } catch (FileException $fileException) {
                $error = new FormError($fileException->getMessage());
                $form->get('image')->addError($error);
            }
        }

        return $this->render('experience/create.html.twig', [
            'title' => $this->translator->trans('Update working place', [], 'experience'),
            'form' => $form->createView(),
            'experience' => $experience,
            'submit' => $this->translator->trans('Update'),
        ]);
    }

    public function tree(Request $request): Response
    {
        $experiences = $this->entityManager->getRepository(Experience::class)->findBy(
            ['locale' => $request->getLocale()],
            ['fromDate' => 'DESC']
        );

        return $this->render('experience/tree.html.twig', [
            'title' => $this->translator->trans('My working experience', [], 'experience'),
            'experiences' => $experiences,
        ]);
    }

    public function delete(Request $request): Response
    {
        $ids = $this->arrayService->getIntegerIds($request->get('id'));
        if (count($ids)) {
            /**
             * @var [] $experiences
             */
            $experiences = $this->entityManager->getRepository(Experience::class)->findBy(['id' => $ids]);
            if (count($experiences)) {
                foreach ($experiences as $experience) {
                    try {
                        if ($experience->getImage()) {
                            $this->fileService->remove($experience->getImage(), $this->parameterBag->get('images_experience'));
                        }
                        $this->entityManager->remove($experience);
                        $this->logger->debug('Experience "'.$experience->getName().'" removed.');
                    } catch (FileNotFoundException $exception) {
                        $this->logger->error($exception->getMessage());
                        $this->addFlash(self::$success, $this->translator->trans('Can not remove image of the company.', [], 'experience'));

                        return $this->redirectToRoute('experience-list');
                    }
                }
                $this->entityManager->flush();
            }
        }

        return $this->redirectToRoute('experience-list');
    }
}
