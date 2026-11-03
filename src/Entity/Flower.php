<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Flower
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'creation_date')]
    private \DateTimeImmutable $creationDate;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\ManyToOne(inversedBy: 'flowers')]
    #[ORM\JoinColumn(nullable: false)]
    private Grave $grave;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreationDate(): \DateTimeImmutable
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeImmutable $creationDate): static
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getGrave(): Grave
    {
        return $this->grave;
    }

    public function setGrave(Grave $grave): static
    {
        $this->grave = $grave;

        return $this;
    }
}
