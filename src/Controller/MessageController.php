<?php

namespace App\Controller;

use App\Entity\Message;
use App\Repository\MessageRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class MessageController extends AbstractController
{
    public const PER_PAGE = 20;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function index(Request $request): Response
    {
        /**
         * @var MessageRepository $messagesRepo
         */
        $messagesRepo = $this->entityManager->getRepository(Message::class);

        $page = (int) $request->get('page', 1);

        $count = $messagesRepo->countMessages();

        $messages = $messagesRepo->findBy([], [
            'seen' => Criteria::ASC,
            'createdAt' => Criteria::DESC,
        ], self::PER_PAGE, ($page - 1) * self::PER_PAGE);

        return $this->render('messages/index.html.twig', [
            'messages' => $messages,
            'title' => $this->translator->trans('Messages', [], 'message'),
            'totalPages' => ceil($count / self::PER_PAGE),
            'page' => $page,
        ]);
    }

    public function view(Request $request): Response
    {
        /**
         * @var Message $message
         */
        $message = $this->entityManager->getRepository(Message::class)->find(
            (int) $request->get('id')
        );

        if (empty($message)) {
            throw new NotFoundHttpException('Message for id "'.(int) $request->get('id').'" not found.');
        }

        if (!$message->isSeen()) {
            $message->setSeen(true);
            $this->entityManager->flush();
        }

        return $this->render('messages/view.html.twig', [
            'title' => $this->translator->trans('Message view', [], 'contactme'),
            'message' => $message,
        ]);
    }

    public function delete(Request $request): Response
    {
        $id = $request->get('id');
        if (is_array($id)) {
            $id = array_unique(array_map('intval', array_keys($id)));
        } else {
            $id = (int) $id;
        }
        $messages = $this->entityManager->getRepository(Message::class)->findBy(['id' => $id]);

        foreach ($messages as $message) {
            $this->entityManager->remove($message);
        }
        $this->entityManager->flush();

        return $this->redirectToRoute('message-list');
    }

    public function seen(Request $request): Response
    {
        $id = $request->get('id');
        if (!empty($id)) {
            if (is_array($id)) {
                $id = array_unique(array_map('intval', array_keys($id)));
            } else {
                throw new NotFoundHttpException('message id is not valid');
            }
            $messages = $this->entityManager->getRepository(Message::class)->findBy(['id' => $id]);

            foreach ($messages as $message) {
                $message->setSeen(true);
            }
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('message-list');
    }
}
