<?php

namespace App\Security;

use App\Entity\RememberMeToken;
use App\Repository\RememberMeTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\RememberMe\PersistentTokenInterface;
use Symfony\Component\Security\Core\Authentication\RememberMe\TokenProviderInterface;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;

readonly class AppTokenProvider implements TokenProviderInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function loadTokenBySeries(string $series): RememberMeToken|PersistentTokenInterface
    {
        /**
         * @var RememberMeTokenRepository $rememberMeTokenRepo
         * @var RememberMeToken           $rememberMeToken
         */
        $rememberMeTokenRepo = $this->entityManager->getRepository(RememberMeToken::class);
        $rememberMeToken = $rememberMeTokenRepo->findBySeries($series);

        if ($rememberMeToken) {
            return $rememberMeToken;
        }

        throw new TokenNotFoundException('No token found.');
    }

    public function deleteTokenBySeries(string $series): void
    {
        /**
         * @var RememberMeTokenRepository $rememberMeTokenRepo
         */
        $rememberMeTokenRepo = $this->entityManager->getRepository(RememberMeToken::class);
        $rememberMeTokenRepo->deleteBySeries($series);
    }

    public function updateToken(string $series, string $tokenValue, \DateTime|\DateTimeInterface $lastUsed): void
    {
        $rememberMeToken = $this->loadTokenBySeries($series);
        $rememberMeToken->setLastUsed($lastUsed);
        $rememberMeToken->setValue($tokenValue);
        $this->entityManager->persist($rememberMeToken);
        $this->entityManager->flush();
    }

    public function createNewToken(PersistentTokenInterface $token): void
    {
        $rememberMeToken = new RememberMeToken();
        $rememberMeToken
            ->setValue($token->getTokenValue())
            ->setLastUsed($token->getLastUsed())
            ->setClass($token->getClass())
            ->setSeries($token->getSeries())
            ->setUsername($token->getUsername());
        $this->entityManager->persist($rememberMeToken);
        $this->entityManager->flush();
    }
}
