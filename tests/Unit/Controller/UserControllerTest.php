<?php

namespace App\Tests\Unit\Controller;

use App\Controller\UserController;
use App\Entity\Role;
use App\Service\Strings;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBag;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserControllerTest extends TestCase
{
    private TranslatorInterface|MockObject $translator;
    private LoggerInterface|MockObject $logger;
    private EntityManagerInterface|MockObject $entityManager;
    private Request|MockObject $request;
    private UserPasswordHasherInterface|MockObject $passwordHasher;
    private ContainerInterface|MockObject $container;
    private FormInterface|MockObject $form;
    private FormFactoryInterface|MockObject $formFactory;
    private FormFactoryInterface|MockObject $eventDispatcher;
    private FormFactoryInterface|MockObject $strings;
    private UserController $sut;

    public function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->request = $this->createMock(Request::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->container = $this->createMock(ContainerInterface::class);
        $this->form = $this->createMock(FormInterface::class);
        $this->formFactory = $this->createMock(FormFactoryInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->strings = $this->createMock(Strings::class);

        $this->sut = new UserController(
            $this->translator,
            $this->logger,
            $this->entityManager,
            $this->eventDispatcher,
            $this->strings
        );

        $this->sut->setContainer($this->container);
        parent::setUp();
    }

    public function testCreateSuccess()
    {
        $role = $this->createMock(Role::class);
        $roleRepository = $this->createMock(EntityRepository::class);
        $roleRepository->expects($this->once())
            ->method('findOneBy')
            ->willReturn($role);

        $tokenStorage = $this->createMock(TokenStorageInterface::class);

        $parameterBag = $this->createMock(ContainerBag::class);
        $parameterBag->method('get')
            ->willReturn(true);

        $router = $this->createMock(RouterInterface::class);
        $router->expects($this->once())->method('generate')->willReturn('testPath');

        $this->container->method('has')
            ->willReturn(true);

        $this->container
            ->method('get')
            ->willReturnOnConsecutiveCalls($tokenStorage, $parameterBag, $this->formFactory, $router);

        $this->formFactory->expects($this->once())
            ->method('create')
            ->willReturnCallback(function ($type, $user) {
                $user->setPlainPassword('test');

                return $this->form;
            });

        $this->form->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(true);

        $this->form->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->willReturn($roleRepository);

        /**
         * @var RedirectResponse $response
         */
        $response = $this->sut->create($this->request, $this->passwordHasher);
        $this->assertEquals('testPath', $response->getTargetUrl());
    }
}
