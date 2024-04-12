<?php

namespace App\Security;

use App\Entity\RememberMeToken;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\RememberMe\PersistentTokenInterface;
use Symfony\Component\Security\Core\Authentication\RememberMe\TokenProviderInterface;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;

readonly class AppTokenProvider implements TokenProviderInterface
{
    public function __construct(private EntityManagerInterface $entityManager, private LoggerInterface $logger)
    {
    }

    public function loadTokenBySeries(string $series): RememberMeToken
    {
        $rememberMeToken = $this->entityManager->getRepository(RememberMeToken::class)->findOneBy(['series' => $series]);

        if ($rememberMeToken) {
            return $rememberMeToken;
        }

        throw new TokenNotFoundException('No token found.');
    }

    public function deleteTokenBySeries(string $series): void
    {
        try {
            $rememberMeToken = $this->loadTokenBySeries($series);
            $this->entityManager->remove($rememberMeToken);
            $this->entityManager->flush();
        } catch (TokenNotFoundException $e) {
            $this->logger->info($e->getMessage());
        }
    }

    public function updateToken(string $series, string $tokenValue, \DateTime|\DateTimeInterface $lastUsed): void
    {
        if (!$lastUsed instanceof \DateTime) {
            // this is a symfony bug: parent interface gives DateTimeInterface but PersistentTokenInterface
            // wants DateTime
            throw new \Exception('Wrong instance of last used.');
        }

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
            ->setUsername($token->getUserIdentifier());
        $this->entityManager->persist($rememberMeToken);
        $this->entityManager->flush();
    }
}
