<?php

namespace App\Controller;

use App\Entity\Message;
use App\Form\ContactMeType;
use App\Traits\FlashMessageTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @psalm-api
 */
class ContactMeController extends AbstractController
{
    use FlashMessageTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
    ) {
    }

    public function index(Request $request): Response
    {
        $message = new Message();
        $form = $this->createForm(ContactMeType::class, $message);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $token = $request->get('contact_me')['_token'];
            if (!$this->isCsrfTokenValid('message', $token)) {
                $this->addFlash(self::ERROR, $this->translator->trans('Some error happened. Please try again.'));
                $this->csrfTokenManager->refreshToken('message');
                $this->csrfTokenManager->removeToken('message');

                return $this->redirectToRoute('contact-me');
            } elseif ($form->isValid()) {
                $this->csrfTokenManager->removeToken('message');
                $message->setCreatedAt(new \DateTimeImmutable());
                $this->entityManager->persist($message);
                $this->entityManager->flush();
                $this->addFlash(self::SUCCESS, $this->translator->trans('Thanks for the message. I\'ll do my best to reach out to you as soon as possible.', [], 'contactme'));

                return $this->redirectToRoute('home');
            }
        }

        return $this->render('contact_me/index.html.twig', [
            'form' => $form->createView(),
            'title' => $this->translator->trans('Contact me', [], 'contactme'),
        ]);
    }
}
