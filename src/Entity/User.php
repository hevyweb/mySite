<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: 'App\Repository\UserRepository')]
#[UniqueEntity(fields: 'email', message: 'This email has been already registered.')]
#[UniqueEntity(fields: 'username', message: 'This username has been already used.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id;

    #[ORM\Column(type: 'string', length: 32, nullable: false)]
    private ?string $firstName;

    #[ORM\Column(type: 'string', length: 32, nullable: false)]
    private ?string $lastName;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Assert\Type('\DateTimeInterface')]
    private ?\DateTime $birthday;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $sex;

    #[ORM\Column(type: 'string', length: 32, unique: true)]
    private ?string $username;

    #[ORM\Column(type: 'string', length: 64, unique: true)]
    private ?string $email;

    #[Assert\Length(max: 32)]
    private ?string $plainPassword;

    #[ORM\Column(type: 'string', length: 64)]
    private ?string $password;

    #[ORM\ManyToMany(targetEntity: Role::class)]
    #[ORM\JoinTable(name: 'user_role')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Collection $roles;

    #[ORM\Column(type: 'boolean')]
    private bool $enabled = true;

    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    private ?string $recovery;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $recoveredAt;

    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $active = false;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $updatedAt;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $updatedBy;

    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    private ?string $emailConfirm;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: EmailHistory::class, orphanRemoval: true)]
    private Collection $emailHistories;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->emailHistories = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @param string|null $firstName
     * @return User
     */
    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @param string|null $lastName
     * @return User
     */
    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getFullName(): string
    {
        return (string) $this->getFirstName() . ' ' . $this->getLastName();
    }

    /**
     * @return \DateTime|null
     */
    public function getBirthday(): ?\DateTime
    {
        return $this->birthday;
    }

    /**
     * @param \DateTime|null $birthday
     * @return User
     */
    public function setBirthDay(?\DateTime $birthday): self
    {
        $this->birthday = $birthday;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getSex(): ?int
    {
        return $this->sex;
    }

    /**
     * @param int|null $sex
     * @return User
     */
    public function setSex(?int $sex): self
    {
        $this->sex = $sex;
        return $this;
    }

    public function setRoles(ArrayCollection $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * Returns the roles granted to the user.
     *
     * @return array roles assigned to user
     */
    public function getRoles(): array
    {
        $roles = $this->roles->toArray();
        return array_map('strval', $roles);
    }

    /**
     * Assign new role to the user
     *
     * @param Role $role
     * @return User
     */
    public function addRole(Role $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
        }
        return $this;
    }

    /**
     * Remove role
     *
     * @param string $role
     * @return User
     */
    public function removeRole(string $role): self
    {
        $this->roles->removeElement($role);
        return $this;
    }

    /**
     * @param string $role
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        foreach ($this->roles as $userRole) {
            if ($userRole->getCode() == $role) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string|null The password
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return User
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string|null The username
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @param string|null $username
     * @return User
     */
    public function setUsername(?string $username): self
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * @param string|null $password
     * @return User
     */
    public function setPlainPassword(?string $password): self
    {
        $this->plainPassword = $password;
        return $this;
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string|null The username
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     * @return User
     */
    public function setEmail(?string $email): self
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getRecovery(): ?string
    {
        return $this->recovery;
    }

    public function setRecovery(?string $recovery): self
    {
        $this->recovery = $recovery;

        return $this;
    }

    public function getRecoveredAt(): ?\DateTimeInterface
    {
        return $this->recoveredAt;
    }

    public function setRecoveredAt(?\DateTimeInterface $recoveredAt): self
    {
        $this->recoveredAt = $recoveredAt;

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUpdatedBy(): ?self
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?self $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    public function getEmailConfirm(): ?string
    {
        return $this->emailConfirm;
    }

    public function setEmailConfirm(?string $emailConfirm): self
    {
        $this->emailConfirm = $emailConfirm;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return 'id';
    }

    /**
     * @return Collection<int, EmailHistory>
     */
    public function getEmailHistories(): Collection
    {
        return $this->emailHistories;
    }

    public function addEmailHistory(EmailHistory $emailHistory): self
    {
        if (!$this->emailHistories->contains($emailHistory)) {
            $this->emailHistories[] = $emailHistory;
            $emailHistory->setUser($this);
        }

        return $this;
    }

    public function removeEmailHistory(EmailHistory $emailHistory): self
    {
        if ($this->emailHistories->removeElement($emailHistory)) {
            // set the owning side to null (unless already changed)
            if ($emailHistory->getUser() === $this) {
                $emailHistory->setUser(null);
            }
        }

        return $this;
    }
}
