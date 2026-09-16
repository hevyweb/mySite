<?php

namespace App\Controller;

use App\DTO\GraveTab;
use App\Entity\Condolence;
use App\Entity\Grave;
use App\Entity\PersonPhoto;
use App\Form\CondolenceType;
use App\Form\GravePhotoType;
use App\Form\GraveType;
use App\Service\FileSystem\File;
use App\Service\ImageService;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class NecropolisController extends AbstractController
{
    const THUMBNAIL_WIDTH = 256; //pixels
    const THUMBNAIL_HEIGHT = 256; //pixels
    
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
        private readonly File $fileService,
        private readonly ParameterBagInterface $parameterBag,
        private readonly ImageService $imageService,
    )  {
    }
    
    public function index(): Response
    {
        $graves = $this->entityManager->getRepository(Grave::class)->findAll();
        return $this->render('necropolis/index.html.twig', [
            'title' => $this->translator->trans('Add working place', [], 'experience'),
            'graves' => $graves,
        ]);
    }
    
    public function create(Request $request): Response
    {
        $grave = new Grave();
        $form = $this->createForm(GraveType::class, $grave);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('image')->getData();
            if ($file) {
                $file = $this->fileService->saveFileTo($file, $this->parameterBag->get('images_necropolis'));
                $grave->setImage($file->getFilename());
            }
            $grave->setCreatedAt(new \DateTimeImmutable());
            $this->entityManager->persist($grave);
            $this->entityManager->flush();

            return $this->redirectToRoute('necropolis-list');
        }

        return $this->render('necropolis/create.html.twig', [
            'title' => $this->translator->trans('Add grave details', [], 'necropolis'),
            'form' => $form->createView(),
            'submit' => $this->translator->trans('Create'),
            'tabs' => $this->buildTabsArray($request),
        ]);
    }
    
    public function grave(Request $request): Response
    {
        $grave = $this->entityManager->getRepository(Grave::class)->find($request->get('id'));
        if ($grave && $grave->isPublished()) {
            $form = $this->createForm(CondolenceType::class);
            
            return $this->render('necropolis/view.html.twig', [
                'grave' => $grave,
                'condolences' => $this->entityManager->getRepository(Condolence::class)->findBy(['grave' => $grave, 'isActive' => true], ['creationDate' => 'DESC']),
                'form' => $form->createView(),
            ]);
        }
        
        throw $this->createNotFoundException();
    }
    
    public function edit(Grave $grave, Request $request): Response
    {
        $form = $this->createForm(GraveType::class, $grave);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('image')->getData();
            if ($file) {
                $file = $this->fileService->saveFileTo($file, $this->parameterBag->get('images_necropolis'));
                if ($grave->getImage()) {
                    $this->fileService->remove($grave->getImage(), $this->parameterBag->get('images_necropolis'));
                }
                $grave->setImage($file->getFilename());
            }
            $grave->setCreatedAt(new \DateTimeImmutable());
            $this->entityManager->persist($grave);
            $this->entityManager->flush();

            return $this->redirectToRoute('necropolis-list');
        }

        return $this->render('necropolis/create.html.twig', [
            'title' => $this->translator->trans('Update grave details', [], 'necropolis'),
            'form' => $form->createView(),
            'grave' => $grave,
            'submit' => $this->translator->trans('Update'),
            'tabs' => $this->buildTabsArray($request),
        ]);
    }
    
    public function delete(Grave $grave): Response
    {
        $this->fileService->remove($grave->getImage(), $this->parameterBag->get('images_necropolis'));
        $this->entityManager->remove($grave);
        $this->entityManager->flush();
        
        return $this->redirectToRoute('necropolis-list');
    }
    
    public function photos(Grave $grave, Request $request): Response
    {
        $form = $this->createForm(GravePhotoType::class, new PersonPhoto(), [
            'action' => $this->generateUrl('necropolis-photos-create', ['id' => $grave->getId()]),
        ]);

        return $this->render('necropolis/photos.html.twig', [
            'grave' => $grave,
            'form' => $form->createView(),
            'title' => $this->translator->trans('Image', [], 'necropolis'),
            'tabs' => $this->buildTabsArray($request),
        ]);
    }
    
    public function photosCreate(Grave $grave, Request $request): Response
    {
        $form = $this->createForm(GravePhotoType::class, new PersonPhoto());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $photo = $form->getData();
            $file = $form->get('filename')->getData();
            if ($file) {
                try {
                    $file = $this->fileService->saveFileTo($file, $this->parameterBag->get('images_necropolis'));
                    $photo->setFilename($file->getFilename());
                    $thumbnailContent = $this->imageService->thumbnail(
                        $this->parameterBag->get('images_necropolis') . '/' . $file->getFilename(),
                        self::THUMBNAIL_WIDTH,
                        self::THUMBNAIL_HEIGHT,
                    );
                    $this->fileService->saveFileContents($thumbnailContent, $this->parameterBag->get('images_necropolis') . '/thumbnails/', $file->getFilename());
                } catch (\Exception $e) {
                    $this->fileService->remove($file->getFilename(), $this->parameterBag->get('images_necropolis'));
                    return $this->redirectToRoute('necropolis-photos', ['id' => $grave->getId()]);
                }
            }
            $photo->setGrave($grave);
            $this->entityManager->persist($photo);
            $this->entityManager->flush();
        }
        return $this->redirectToRoute('necropolis-photos', ['id' => $grave->getId()]);
    }
    
    public function photosList(Grave $grave): Response
    {
        return $this->json($grave->getPersonPhotos(),
            200,
            [],
            ['groups' => ['photo:read']]
        );
    }
    
    public function condolences(Request $request, Grave $grave): Response
    {   
        return $this->render('necropolis/condolences.html.twig', [
            'grave' => $grave,
            'title' => $this->translator->trans('Condolences', [], 'necropolis'),
            'tabs' => $this->buildTabsArray($request),
            ]
        );
    }
    
    public function condolencesDelete(Request $request): Response
    {       
        $condolenceId = $request->get('condolenceId');
        $condolence = $this->entityManager->getRepository(Condolence::class)->find($condolenceId);
        if (!$condolence) {
            $this->addFlash('error', 'Grave condolence not found');
            return $this->redirectToRoute('necropolis-list');
        }
        
        $this->entityManager->remove($condolence);
        $this->entityManager->flush();
        $this->entityManager->clear();
        return $this->redirectToRoute('necropolis-condolences', ['id' => $condolence->getGrave()->getId()]);
    }

    public function condolencesNew(Request $request, Grave $grave): JsonResponse
    {
        $form = $this->createForm(CondolenceType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $condolence = $form->getData();
            $condolence->setCreationDate(new \DateTimeImmutable());
            $condolence->setGrave($grave);
            $this->entityManager->persist($condolence);
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'message' => $this->translator->trans('Condolence added successfully', [], 'necropolis'),
            ], Response::HTTP_OK);
        }

        // Collect validation errors into a key-value array [fieldName => [errorMessages]]
        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $origin = $error->getOrigin();
            $fieldName = $origin ? $origin->getName() : 'global';
            $errors[$fieldName][] = $error->getMessage();
        }

        return new JsonResponse([
            'success' => false,
            'errors' => $errors,
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
    
    private function buildTabsArray(Request $request): array
    {
        $tabs = [];
        if ($request->get('id')) {
            $mainTab = new GraveTab();
            $mainTab->label = $this->translator->trans('Grave details', [], 'necropolis');
            $mainTab->url = $this->generateUrl('necropolis-create', ['id' => $request->get('id')]);
            $mainTab->active = $request->attributes->get('_route') === 'necropolis-edit';
            $mainTab->icon = 'fa fa-pencil';
            $tabs[] = $mainTab;
            
            $photoTab = new GraveTab();
            $photoTab->label = $this->translator->trans('Image', [], 'necropolis');
            $photoTab->url = $this->generateUrl('necropolis-photos', ['id' => $request->get('id')]);
            $photoTab->active = $request->attributes->get('_route') === 'necropolis-photos';
            $photoTab->icon = 'fa fa-image';
            $tabs[] = $photoTab;
            
            $condolenceTab = new GraveTab();
            $condolenceTab->label = $this->translator->trans('Condolences', [], 'necropolis');
            $condolenceTab->url = $this->generateUrl('necropolis-condolences', ['id' => $request->get('id')]);
            $condolenceTab->active = $request->attributes->get('_route') === 'necropolis-condolences';
            $condolenceTab->icon = 'fa fa-heart';
            $tabs[] = $condolenceTab;
            
            $flowersTab = new GraveTab();
            $flowersTab->label = $this->translator->trans('Flowers', [], 'necropolis');
            $flowersTab->url = $this->generateUrl('necropolis-flowers', ['id' => $request->get('id')]);
            $flowersTab->active = $request->attributes->get('_route') === 'necropolis-flowers';
            $flowersTab->icon = 'fa fa-gift';
            $tabs[] = $flowersTab;
        }
        return $tabs;
    }
    
    public function photosDelete(Request $request): Response
    {
        $id = $request->get('id');
        $grave = $this->entityManager->getRepository(Grave::class)->find($id);
        if (!$grave) {
            $this->addFlash('error', 'Grave not found');
            return $this->redirectToRoute('necropolis-list');
        }

        $photoId = $request->get('photoId');
        $personPhoto = $this->entityManager->getRepository(PersonPhoto::class)->find($photoId);
        if (!$personPhoto) {
            $this->addFlash('error', 'Grave photo not found');
            return $this->redirectToRoute('necropolis-photos', ['id' => $grave->getId()]);
        }

        $this->fileService->remove($personPhoto->getFilename(), $this->parameterBag->get('images_necropolis').'/thumbnails/');
        $this->fileService->remove($personPhoto->getFilename(), $this->parameterBag->get('images_necropolis'));
        
        $this->entityManager->remove($personPhoto);
        $this->entityManager->flush();
        $this->entityManager->clear();
        
        return $this->redirectToRoute('necropolis-photos', ['id' => $grave->getId()]);
    }
    
    public function photosEdit(Request $request): Response
    {
        $id = $request->get('id');
        $grave = $this->entityManager->getRepository(Grave::class)->find($id);
        if (!$grave) {
            $this->addFlash('error', 'Grave not found');
            return $this->redirectToRoute('necropolis-list');
        }
        
        $photoId = $request->get('photoId');
        $personPhoto = $this->entityManager->getRepository(PersonPhoto::class)->find($photoId);
        if (!$personPhoto) {
            $this->addFlash('error', 'Grave photo not found');
            return $this->redirectToRoute('necropolis-photos', ['id' => $grave->getId()]);
        }
        
        if ($personPhoto->getGrave() !== $grave) {
            $this->addFlash('error', 'Grave photo does not belong to this grave');
            return $this->redirectToRoute('necropolis-photos', ['id' => $grave->getId()]);
        }
        
        $description = $request->get('description');
        $shootingDate = $request->get('shootingDate');
        if ($description) {
            $personPhoto->setDescription($description);
        }
        
        if ($shootingDate) {
            $personPhoto->setShootingDate(new \DateTime($shootingDate));
        }
        
        $this->entityManager->flush();
        $this->entityManager->clear();
        
        return $this->redirectToRoute('necropolis-photos', ['id' => $grave->getId()]);
    }
    
    public function condolencesToggleStatus(Request $request): Response
    {
        $condolence = $this->entityManager->getRepository(Condolence::class)->find($request->get('condolenceId'));
        if (!$condolence) {
            $this->addFlash('error', 'Grave condolence not found');
            return $this->redirectToRoute('necropolis-list');
        }
        
        $condolence->setIsActive(!$condolence->isActive());
        $this->entityManager->flush();
        return $this->redirectToRoute('necropolis-condolences', ['id' => $condolence->getGrave()->getId()]);
    }

    public function qrcode(Grave $grave): Response
    {
        $writer = new PngWriter();

        $qrCode = new QrCode(
            data: $this->generateUrl('necropolis-public', ['id' => $grave->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Low,
            size: 900,
            margin: 0, // Прибираємо внутрішній margin ендроїда, відступи контролюємо через Imagick
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: new Color(44, 44, 44),
            backgroundColor: new Color(255, 255, 255)
        );

        $result = $writer->write($qrCode);

        // 1. Початкове зображення QR-коду
        $qrImage = new \Imagick();
        $qrImage->readImageBlob($result->getString());

        $qrWidth = $qrImage->getImageWidth();
        $qrHeight = $qrImage->getImageHeight();

        $strokeWidth = 50;
        $halfStroke = $strokeWidth / 2;

        // Висота верхнього та нижнього текстових блоків
        $topTextBlockHeight = 110;
        $bottomTextBlockHeight = 130;

        // 2. Вираховуємо розмір фінального КВАДРАТА
        // Загальна висота = QR + рамки + верхній текст + нижній текст
        $squareSize = $qrHeight + ($strokeWidth * 2) + $topTextBlockHeight + $bottomTextBlockHeight;

        // Розраховуємо бічні відступи (padding), щоб QR-код був по центру
        $sidePadding = ($squareSize - ($strokeWidth * 2) - $qrWidth) / 2;

        // Радіус закруглення зовнішньої рамки (5% від розміру квадрата)
        $outerRadius = $squareSize * 0.05;

        // 3. Створюємо квадратне полотно з білим фоном
        $finalImage = new \Imagick();
        $finalImage->newImage($squareSize, $squareSize, new \ImagickPixel('white'), 'png');

        // Накладаємо QR-код строго по центру
        $qrX = $strokeWidth + $sidePadding;
        $qrY = $strokeWidth + $topTextBlockHeight;
        $finalImage->compositeImage($qrImage, \Imagick::COMPOSITE_OVER, (int)$qrX, (int)$qrY);

        // 4. Малюємо зовнішню рамку із закругленими кутами навколо всього квадрата
        $borderDraw = new \ImagickDraw();
        $borderDraw->setFillColor(new \ImagickPixel('none'));
        $borderDraw->setStrokeColor(new \ImagickPixel('#2c2c2c'));
        $borderDraw->setStrokeWidth($strokeWidth);

        $x1 = $halfStroke;
        $y1 = $halfStroke;
        $x2 = $squareSize - $halfStroke;
        $y2 = $squareSize - $halfStroke;

        $borderDraw->roundRectangle($x1, $y1, $x2, $y2, $outerRadius, $outerRadius);
        $finalImage->drawImage($borderDraw);

        // Налаштування шрифту
        $fontPath = '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';
        $fontToUse = file_exists($fontPath) ? $fontPath : 'Helvetica-Bold';

        // 5. Текст зверху: "Вічна пам'ять"
        $topText = "Вічна пам'ять";
        $topTextDraw = new \ImagickDraw();
        $topTextDraw->setFillColor(new \ImagickPixel('#2c2c2c'));
        $topTextDraw->setTextAlignment(\Imagick::ALIGN_CENTER);
        $topTextDraw->setFont($fontToUse);

        // Підбір розміру для верхнього тексту
        $topFontSize = 52;
        $maxTextWidth = $squareSize - (($strokeWidth + 30) * 2);
        do {
            $topTextDraw->setFontSize($topFontSize);
            $topMetrics = $finalImage->queryFontMetrics($topTextDraw, $topText);
            $topFontSize -= 2;
        } while ($topMetrics['textWidth'] > $maxTextWidth && $topFontSize > 18);

        $topY = $strokeWidth + ($topTextBlockHeight / 2) + ($topMetrics['ascender'] / 2) - 5;
        $finalImage->annotateImage($topTextDraw, $squareSize / 2, $topY, 0, $topText);

        // 6. Текст знизу: Ім'я та Прізвище
        $fullName = trim($grave->getFirstName() . ' ' . $grave->getLastName());

        if (!empty($fullName)) {
            $bottomTextDraw = new \ImagickDraw();
            $bottomTextDraw->setFillColor(new \ImagickPixel('#2c2c2c'));
            $bottomTextDraw->setTextAlignment(\Imagick::ALIGN_CENTER);
            $bottomTextDraw->setFont($fontToUse);

            $bottomFontSize = 60;
            do {
                $bottomTextDraw->setFontSize($bottomFontSize);
                $bottomMetrics = $finalImage->queryFontMetrics($bottomTextDraw, $fullName);
                $bottomFontSize -= 2;
            } while ($bottomMetrics['textWidth'] > $maxTextWidth && $bottomFontSize > 18);

            $bottomAreaTop = $qrY + $qrHeight;
            $bottomY = $bottomAreaTop + ($bottomTextBlockHeight / 2) + ($bottomMetrics['ascender'] / 2) - 5;

            $finalImage->annotateImage($bottomTextDraw, $squareSize / 2, $bottomY, 0, $fullName);
        }

        return $this->json([
            'qrcode' => base64_encode($finalImage->getImageBlob()),
        ]);
    }
}
