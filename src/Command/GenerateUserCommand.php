<?php

namespace App\Command;

use App\Entity\Role;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:generate:user',
    description: 'This command generates admin user.',
)]
class GenerateUserCommand extends Command
{
    private EntityManagerInterface $entityManager;

    private UserPasswordHasherInterface $passwordEncoder;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordEncoder)
    {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->addOption(
                'username',
                'u',
                InputOption::VALUE_OPTIONAL,
                'Username',
                'admin'
            )
            ->addOption(
                'password',
                'p',
                InputOption::VALUE_OPTIONAL,
                'User password',
                'admin'
            );
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $username = $input->getOption('username');
        $plainPassword = $input->getOption('password');

        $userRepository = $this->entityManager->getRepository(User::class);

        if ($userRepository->findOneBy(['username' => $username])) {
            $io->error('User with username "'.$username.'" already exists.');

            return 1;
        }
        $user = new User();
        $this->assignRoles($user);
        $this->assignUniqueEmail($user);
        $user
            ->setUsername($username)
            ->setFirstName($username)
            ->setLastName($username)
            ->setPlainPassword($plainPassword)
            ->setEnabled(true)
            ->setActive(true)
            ->setCreatedAt(new \DateTimeImmutable());
        $this->encodePassword($user);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success('New admin user created. Username: '.$username.', password: '.$plainPassword);

        return 0;
    }

    protected function assignUniqueEmail(User $user): User
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        do {
            $email = 'Change-me'.uniqid();
        } while ($userRepository->findOneBy(['email' => $email]));

        return $user->setEmail($email);
    }

    /**
     * @throws \RuntimeException
     */
    private function assignRoles(User $user): void
    {
        $roleRepository = $this->entityManager->getRepository(Role::class);
        $adminRole = $roleRepository->findOneBy(['code' => 'ROLE_ADMIN']);
        $userRole = $roleRepository->findOneBy(['code' => 'ROLE_USER']);

        if (empty($adminRole) || empty($userRole)) {
            throw new \RuntimeException('Run migrations first. Roles have not been set yet');
        }

        $user
            ->addRole($adminRole)
            ->addRole($userRole);
    }

    private function encodePassword(User $user): void
    {
        $password = $this->passwordEncoder->hashPassword($user, $user->getPlainPassword());

        $user->setPassword($password);
    }
}
