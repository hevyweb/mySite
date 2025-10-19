<?php

namespace App\Controller;

use App\DTO\UserSearch;
use App\Entity\EmailHistory;
use App\Entity\Role;
use App\Entity\User;
use App\Event\NewEmailConfirmEvent;
use App\Event\OldEmailConfirmEvent;
use App\Event\RecoverPasswordEvent;
use App\Event\ResetPasswordEvent;
use App\Event\SignUpEvent;
use App\Exception\BrutForceException;
use App\Exception\UserNotFoundException;
use App\Form\User\EditUserType;
use App\Form\User\RecoverPasswordType;
use App\Form\User\RegistrationType;
use App\Form\User\UserPasswordsType;
use App\Service\StringService;
use App\Traits\FlashMessageTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @psalm-api
 */
class UserController extends AbstractController
{
    use FlashMessageTrait;

    public const LIMIT = 20;

    public const RECOVERY_COOL_DOWN = 86400; // 24 hours

    public const ANTI_BRUT_FORCE_COOL_DOWN = 2000000; // microseconds

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly StringService $strings,
    ) {
    }

    public function index(#[MapQueryString] ?UserSearch $userSearch = new UserSearch()): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $this->entityManager->getRepository(User::class)->search($userSearch),
            'title' => 'Users',
            'totalPages' => ceil($this->entityManager->getRepository(User::class)->total($userSearch) / $userSearch->limit),
            'page' => $userSearch->page,
            'filterVariables' => ['search' => $userSearch->search],
        ]);
    }

    public function edit(Request $request): Response
    {
        $user = $this->getUserFromRequest($request);
        $userEditForm = $this->createForm(EditUserType::class, $user, [
            'action' => $this->generateUrl('user-edit-general', [
                'id' => $user->getId() != $this->getUser()->getId() ? $user->getId() : null,
            ]),
        ]);

        $oldEmail = $user->getEmail();
        $userEditForm->handleRequest($request);
        if ($userEditForm->isSubmitted() && $userEditForm->isValid()) {
            $this->checkEmailChanged($oldEmail, $user);

            $user
                ->setUpdatedAt(new \DateTime())
                ->setUpdatedBy($this->getUser());
            $this->entityManager->flush();
            $this->addFlash(self::SUCCESS, $this->translator->trans('User data updated.', [], 'user'));
        }

        return $this->render('user/edit.html.twig', [
            'title' => $this->translator->trans('Update user data', [], 'user'),
            'form' => $userEditForm->createView(),
            'submit' => $this->translator->trans('Save'),
            'user' => $user,
            'tabs' => $this->getUserTabs($user),
        ]);
    }

    private function getUserFromRequest(Request $request): User|null
    {
        $userId = (int) $request->get('id');
        if (empty($userId)) {
            return $this->getUser();
        }

        $user = $this->entityManager->getRepository(User::class)->find($userId);

        if (empty($user)) {
            throw new UserNotFoundException('User with id "'.$userId.'" not found.');
        }

        return $user;
    }

    #[\Override]
    public function getUser(): ?User
    {
        $user = parent::getUser();
        if (empty($user)) {
            return null;
        }

        if (!$user instanceof User) {
            throw new \Exception('Wrong user entity provided.');
        }

        return $user;
    }

    private function checkEmailChanged(string $oldEmail, User $user): void
    {
        if ($oldEmail != $user->getEmail()) {
            $emailHistory = $user->getEmailHistories()->filter(
                fn (EmailHistory $emailHistory) => !$emailHistory->getNewEmailConfirmAt() || !$emailHistory->getOldEmailConfirmAt()
            );

            if (!$emailHistory->count()) {
                $emailHistory = new EmailHistory();
                $emailHistory->setCreatedAt(new \DateTimeImmutable())
                    ->setOldConfirmationToken($this->strings->generateRandomSlug(64))
                    ->setOldEmail($oldEmail)
                    ->setUser($user);
                $user->addEmailHistory($emailHistory);
            } else {
                $emailHistory = $emailHistory->first();
            }

            $emailHistory
                ->setNewConfirmationToken($this->strings->generateRandomSlug(64))
                ->setNewEmailConfirmAt(null)
                ->setNewEmail($user->getEmail());

            if (!$this->getUser()->hasRole(Role::ROLE_ADMIN)) {
                $user->setEmail($oldEmail);
                $this->entityManager->persist($emailHistory);
                $this->entityManager->flush();
                $this->dispatcher->dispatch(new NewEmailConfirmEvent($emailHistory));
                $this->dispatcher->dispatch(new OldEmailConfirmEvent($emailHistory));
            } else {
                $emailHistory->setCompleted(true);
                $this->entityManager->persist($emailHistory);
                $this->entityManager->flush();
            }
        }
    }

    /**
     * @return array<string, array{text: string, iconClass: string}>
     */
    private function getUserTabs(User $user): array
    {
        $tabs = [
            'general' => ['text' => 'General', 'iconClass' => 'fa-regular fa-address-card'],
        ];

        if ($user === $this->getUser()) {
            $tabs['password'] = ['text' => 'Password', 'iconClass' => 'fa-solid fa-shield'];
        }

        if ($this->getUser()->hasRole(Role::ROLE_ADMIN)) {
            $tabs['roles'] = ['text' => 'Roles', 'iconClass' => 'fa-solid fa-user-nurse'];
        }

        return $tabs;
    }

    public function editUserPassword(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUserFromRequest($request);
        $userEditForm = $this->createForm(UserPasswordsType::class, null, [
            'action' => $this->generateUrl('user-edit-password'),
        ]);

        $userEditForm->handleRequest($request);
        if ($userEditForm->isSubmitted() && $userEditForm->isValid()) {
            $password = $passwordHasher->hashPassword($user, $userEditForm->get('newPassword')->getData());
            $user->setPassword($password);
            $user
                ->setUpdatedAt(new \DateTime())
                ->setUpdatedBy($this->getUser());
            $this->entityManager->flush();
            $this->addFlash(self::SUCCESS, $this->translator->trans('User password changed.', [], 'user'));
        }

        return $this->render('user/passwords.html.twig', [
            'title' => $this->translator->trans('Change Password', [], 'user'),
            'form' => $userEditForm->createView(),
            'submit' => $this->translator->trans('Save'),
            'user' => $user,
            'tabs' => $this->getUserTabs($user),
        ]);
    }

    public function editUserRoles(Request $request): Response
    {
        $user = $this->getUserFromRequest($request);

        $roleRepository = $this->entityManager->getRepository(Role::class);
        $existingRoles = $roleRepository->findAll();

        if ($request->isMethod('POST')) {
            $rolesIds = $request->get('roles');
            $userRoles = $this->createEmptyCollection();
            if (!is_null($rolesIds) && count($rolesIds)) {
                $roles = $roleRepository->findBy(['id' => $rolesIds]);
                foreach ($roles as $role) {
                    $userRoles->add($role);
                }
            }

            $user->setRoles($userRoles);
            $user
                ->setUpdatedAt(new \DateTime())
                ->setUpdatedBy($this->getUser());

            $this->addFlash(self::SUCCESS, $this->translator->trans('Saved successfully'));
            $this->entityManager->flush();
        }

        return $this->render('user/roles.html.twig', [
            'title' => $this->translator->trans('User Roles', [], 'user'),
            'user' => $user,
            'roles' => $existingRoles,
            'tabs' => $this->getUserTabs($user),
        ]);
    }

    /**
     * This is a crutch for psalm, because it does not line generic collections.
     *
     * @return ArrayCollection<int, Role>
     */
    private function createEmptyCollection(): ArrayCollection
    {
        return new ArrayCollection();
    }

    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('user-edit-general');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('user/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'title' => $this->translator->trans('Sign in', [], 'user'),
        ]);
    }

    public function logout(): Response
    {
        return $this->redirectToRoute('home');
    }

    public function create(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        if (!empty($this->getUser())) {
            return $this->redirectToRoute('user-edit-general');
        }

        if (!$this->getParameter('registration_enabled')) {
            return $this->render('user/register_closed.html.twig', [
                'title' => $this->translator->trans('Sign up'),
            ]);
        }

        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $userRole = $this->entityManager->getRepository(Role::class)->findOneBy(['code' => Role::ROLE_USER]);
            $password = $passwordHasher->hashPassword($user, $form->get('password')->getData());
            $user->setPassword($password)
                ->addRole($userRole)
                ->setEmailConfirm(md5(uniqid()).md5(uniqid()))
                ->setCreatedAt(new \DateTimeImmutable());
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->dispatcher->dispatch(new SignUpEvent($user));

            return $this->redirectToRoute('home');
        }

        return $this->render('user/register.html.twig', [
            'title' => $this->translator->trans('Sign up'),
            'form' => $form->createView(),
        ]);
    }

    public function confirmEmail(string $token): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('user-edit-general');
        }

        $user = $this->entityManager->getRepository(User::class)
            ->findOneBy(['emailConfirm' => $token]);

        if (!empty($user)) {
            $user
                ->setActive(true)
                ->setEmailConfirm(null);

            $this->entityManager->flush();

            $this->addFlash(self::SUCCESS, $this->translator->trans(
                'Your email is confirmed. Enter your username "%username%" and password that you created during registration to login.',
                [
                    '%username%' => $user->getUsername(),
                ],
                'user',
            ));

            return $this->redirectToRoute('home');
        }

        usleep(mt_rand(0, self::ANTI_BRUT_FORCE_COOL_DOWN));

        throw new NotFoundHttpException($this->translator->trans('User not found.', [], 'user'));
    }

    public function recoverPassword(Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('user-edit-general');
        }
        $form = $this->createForm(RecoverPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository = $this->entityManager->getRepository(User::class);
            try {
                $email = $form->get('email')->getData();
                $user = $userRepository->findOneBy(['email' => $email]);

                if (!$user) {
                    usleep(mt_rand(0, self::ANTI_BRUT_FORCE_COOL_DOWN));
                    throw new UserNotFoundException($this->translator->trans('User not found.', [], 'user'));
                }

                if (null !== $user->getRecoveredAt()
                    && $user->getRecoveredAt()->diff(new \DateTime())->s <= self::RECOVERY_COOL_DOWN) {
                    throw new BrutForceException('User "'.$user->getUsername().'" is trying to recover the password too often.');
                }
                $user->setRecoveredAt(new \DateTime());
                $user->setRecovery($this->strings->generateRandomSlug(64));
                $this->entityManager->flush();
                $event = new RecoverPasswordEvent($user);

                $this->dispatcher->dispatch($event);
            } catch (NotFoundHttpException $exception) {
                // we should not display to user any warning to prevent email phishing.
                $this->logger->warning(
                    'Some one is trying to recover password for non existing user "'.($email ?? 'empty email').'". Original message: '.
                    $exception->getMessage()
                );
            } catch (BrutForceException $exception) {
                // we should not display to user any warning to prevent email phishing.
                $this->logger->warning($exception->getMessage());
            }

            $this->addFlash(self::SUCCESS,
                $this->translator->trans(
                    'We have sent an email with a password reset request to the email address provided.',
                    [],
                    'user',
                ));

            return $this->redirectToRoute('home');
        }

        return $this->render('user/recover_password.html.twig', [
            'title' => $this->translator->trans('Recover password', [], 'user'),
            'form' => $form->createView(),
        ]);
    }

    public function resetPassword(
        string $token,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['recovery' => $token]);
        if ($user) {
            $newPassword = $this->strings->generateRandomString();
            $user
                ->setPassword($passwordHasher->hashPassword($user, $newPassword))
                ->setUpdatedAt(new \DateTime())
                ->setUpdatedBy($user)
                ->setRecovery(null)
                ->setRecoveredAt(null);

            $this->entityManager->flush();

            $event = new ResetPasswordEvent($user, $newPassword);
            $this->dispatcher->dispatch($event);
        } else {
            usleep(mt_rand(0, self::ANTI_BRUT_FORCE_COOL_DOWN));
        }
        $this->addFlash(self::SUCCESS, $this->translator->trans(
            'A new password has been sent to your email.',
            [],
            'user'
        ));

        return $this->redirectToRoute('home');
    }

    public function confirmNewEmail(string $token): Response
    {
        $emailHistory = $this->entityManager->getRepository(EmailHistory::class)
            ->findOneBy([
                'user' => $this->getUser(),
                'newConfirmationToken' => $token,
                'completed' => false,
            ]);

        if ($emailHistory) {
            if (!$emailHistory->getNewEmailConfirmAt()) {
                $emailHistory->setNewEmailConfirmAt(new \DateTimeImmutable());
            }
            if (!$this->changeEmail($emailHistory)) {
                $this->addFlash(self::SUCCESS, $this->translator->trans('Old email successfully confirmed.', [], 'user'));
            }
        } else {
            $this->addFlash(self::ERROR, $this->translator->trans('The confirmation link is expired.'));
        }
        $this->entityManager->flush();

        return $this->redirectToRoute('home');
    }

    private function changeEmail(EmailHistory $emailHistory): bool
    {
        if ($emailHistory->getNewEmailConfirmAt() && $emailHistory->getOldEmailConfirmAt()) {
            $emailHistory->setCompleted(true);
            $this->getUser()
                ->setEmail($emailHistory->getNewEmail())
                ->setUpdatedBy($this->getUser())
                ->setUpdatedAt(new \DateTime());

            $this->addFlash(self::SUCCESS, $this->translator->trans('Email has been successfully changed.'));

            return true;
        }

        return false;
    }

    public function confirmOldEmail(string $token): Response
    {
        $emailHistory = $this->entityManager->getRepository(EmailHistory::class)
            ->findOneBy([
                'user' => $this->getUser(),
                'oldConfirmationToken' => $token,
                'completed' => false,
            ]);

        if ($emailHistory) {
            if (!$emailHistory->getOldEmailConfirmAt()) {
                $emailHistory->setOldEmailConfirmAt(new \DateTimeImmutable());
            }
            if (!$this->changeEmail($emailHistory)) {
                $this->addFlash(self::SUCCESS, $this->translator->trans('Old email successfully confirmed.'));
            }
        } else {
            $this->addFlash(self::ERROR, $this->translator->trans('The confirmation link is expired.', [], 'user'));
        }
        $this->entityManager->flush();

        return $this->redirectToRoute('home');
    }
}
