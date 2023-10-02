<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Authentication\RememberMe\PersistentTokenInterface;

#[ORM\Entity(repositoryClass: 'App\Repository\RememberMeTokenRepository')]
class RememberMeToken implements PersistentTokenInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id;

    #[ORM\Column(type: 'string', length: 88, unique: true)]
    private string $series;

    #[ORM\Column(type: 'string', length: 88)]
    private ?string $value;

    #[ORM\Column(name: 'last_used', type: 'datetime')]
    private \DateTime $lastUsed;

    #[ORM\Column(type: 'string', length: 100)]
    private string $class;

    #[ORM\Column(type: 'string', length: 200)]
    private ?string $username;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSeries(): string
    {
        return $this->series;
    }

    public function setSeries(string $series): self
    {
        $this->series = $series;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getLastUsed(): \DateTime
    {
        return $this->lastUsed;
    }

    public function setLastUsed(\DateTime $lastUsed): self
    {
        $this->lastUsed = $lastUsed;

        return $this;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function setClass(string $class): self
    {
        $this->class = $class;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getTokenValue(): string
    {
        return $this->getValue();
    }

    public function getUserIdentifier(): string
    {
        return 'id';
    }
}
