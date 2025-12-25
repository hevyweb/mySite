<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class EmailHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $oldEmail;

    #[ORM\Column(type: 'string', length: 255)]
    private string $newEmail;

    #[ORM\Column(type: 'string', length: 64)]
    private string $oldConfirmationToken;

    #[ORM\Column(type: 'string', length: 64)]
    private string $newConfirmationToken;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $oldEmailConfirmAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $newEmailConfirmAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'emailHistories')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Column(type: 'boolean')]
    private bool $completed = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOldEmail(): string
    {
        return $this->oldEmail;
    }

    public function setOldEmail(string $oldEmail): self
    {
        $this->oldEmail = $oldEmail;

        return $this;
    }

    public function getNewEmail(): string
    {
        return $this->newEmail;
    }

    public function setNewEmail(string $newEmail): self
    {
        $this->newEmail = $newEmail;

        return $this;
    }

    public function getOldConfirmationToken(): string
    {
        return $this->oldConfirmationToken;
    }

    public function setOldConfirmationToken(string $oldConfirmationToken): self
    {
        $this->oldConfirmationToken = $oldConfirmationToken;

        return $this;
    }

    public function getNewConfirmationToken(): string
    {
        return $this->newConfirmationToken;
    }

    public function setNewConfirmationToken(string $newConfirmationToken): self
    {
        $this->newConfirmationToken = $newConfirmationToken;

        return $this;
    }

    public function getOldEmailConfirmAt(): ?\DateTimeImmutable
    {
        return $this->oldEmailConfirmAt;
    }

    public function setOldEmailConfirmAt(?\DateTimeImmutable $oldEmailConfirmAt): self
    {
        $this->oldEmailConfirmAt = $oldEmailConfirmAt;

        return $this;
    }

    public function getNewEmailConfirmAt(): ?\DateTimeImmutable
    {
        return $this->newEmailConfirmAt;
    }

    public function setNewEmailConfirmAt(?\DateTimeImmutable $newEmailConfirmAt): self
    {
        $this->newEmailConfirmAt = $newEmailConfirmAt;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function isCompleted(): bool
    {
        return $this->completed;
    }

    public function setCompleted(bool $completed): self
    {
        $this->completed = $completed;

        return $this;
    }
}
