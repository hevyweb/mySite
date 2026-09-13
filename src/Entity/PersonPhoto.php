<?php

namespace App\Entity;

use App\Repository\PersonPhotoRepository;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PersonPhotoRepository::class)]
class PersonPhoto
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['photo:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 64)]
    #[Groups(['photo:read'])]
    private ?string $filename = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['photo:read'])]
    private ?string $description = null;

    #[ORM\Column(name: 'shooting_date', nullable: true)]
    #[Groups(['photo:read'])]
    private ?\DateTime $shootingDate = null;

    #[ORM\ManyToOne(inversedBy: 'personPhotos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Grave $grave = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getShootingDate(): ?\DateTime
    {
        return $this->shootingDate;
    }

    public function setShootingDate(?\DateTime $shootingDate): static
    {
        $this->shootingDate = $shootingDate;

        return $this;
    }

    public function getGrave(): ?Grave
    {
        return $this->grave;
    }

    public function setGrave(?Grave $grave): static
    {
        $this->grave = $grave;

        return $this;
    }
}
