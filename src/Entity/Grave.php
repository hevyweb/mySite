<?php

namespace App\Entity;

use App\Repository\GraveRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: GraveRepository::class)]
class Grave
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: "doctrine.uuid_generator")]
    #[Assert\Uuid]
    private ?string $id = null;

    #[ORM\Column(name: "first_name", length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(name: "last_name", length: 255)]
    private ?string $lastName = null;

    #[ORM\Column(name: "maiden_name", length: 255, nullable: true)]
    private ?string $maidenName = null;

    #[ORM\Column(name: "birth_date", type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $birthDate = null;

    #[ORM\Column(name: "death_date", type: Types::DATE_MUTABLE)]
    private ?\DateTime $deathDate = null;

    #[ORM\Column(name: "birth_place", length: 255, nullable: true)]
    private ?string $birthPlace = null;

    #[ORM\Column(name: "birth_place_coordinates", length: 515, nullable: true)]
    private ?string $birthPlaceCoordinates = null;

    #[ORM\Column(name: "death_place", length: 255, nullable: true)]
    private ?string $deathPlace = null;

    #[ORM\Column(name: "death_place_coordinates", length: 515, nullable: true)]
    private ?string $deathPlaceCoordinates = null;
    
    #[ORM\Column(name: "grave_place", length: 255, nullable: true)]
    private ?string $gravePlace = null;
    #[ORM\Column(name: "grave_place_coordinates", length: 515, nullable: true)]
    private ?string $gravePlaceCoordinates = null;
    
    #[ORM\Column(name: "epitaph", length: 255, nullable: true)]
    private ?string $epitaph = null;

    #[ORM\Column(length: 255)]
    private ?string $image = null;

    #[ORM\Column(name: "is_published")]
    private ?bool $isPublished = null;
    
    #[ORM\Column(name: "gender")]
    private int $gender;
    
    #[ORM\Column(name: "biography", type: Types::TEXT, nullable: true)]
    private ?string $biography = null;

    #[ORM\Column(name: "created_at")]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: "updated_at", nullable: true)]
    private ?\DateTime $updatedAt = null;

    /**
     * @var Collection<int, PersonPhoto>
     */
    #[ORM\OneToMany(mappedBy: 'grave', targetEntity: PersonPhoto::class, orphanRemoval: true)]
    private Collection $personPhotos;

    /**
     * @var Collection<int, Flower>
     */
    #[ORM\OneToMany(mappedBy: 'grave', targetEntity: Flower::class, orphanRemoval: true)]
    private Collection $flowers;

    /**
     * @var Collection<int, Condolence>
     */
    #[ORM\OneToMany(mappedBy: 'grave', targetEntity: Condolence::class, orphanRemoval: true)]
    private Collection $condolences;

    public function __construct()
    {
        $this->personPhotos = new ArrayCollection();
        $this->flowers = new ArrayCollection();
        $this->condolences = new ArrayCollection();
    }
    
    public function getId(): ?string
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getMaidenName(): ?string
    {
        return $this->maidenName;
    }

    public function setMaidenName(?string $maidenName): self
    {
        $this->maidenName = $maidenName;
        return $this;
    }

    public function getBirthDate(): ?\DateTime
    {
        return $this->birthDate;
    }

    public function setBirthDate(?\DateTime $birthDate): self
    {
        $this->birthDate = $birthDate;
        return $this;
    }

    public function getDeathDate(): ?\DateTime
    {
        return $this->deathDate;
    }

    public function setDeathDate(\DateTime $deathDate): self
    {
        $this->deathDate = $deathDate;
        return $this;
    }

    public function getBirthPlace(): ?string
    {
        return $this->birthPlace;
    }

    public function setBirthPlace(?string $birthPlace): self
    {
        $this->birthPlace = $birthPlace;
        return $this;
    }

    public function getDeathPlace(): ?string
    {
        return $this->deathPlace;
    }

    public function setDeathPlace(?string $deathPlace): self
    {
        $this->deathPlace = $deathPlace;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;
        return $this;
    }

    public function isPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): self
    {
        $this->isPublished = $isPublished;
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

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getBirthPlaceCoordinates(): ?string
    {
        return $this->birthPlaceCoordinates;
    }

    public function setBirthPlaceCoordinates(?string $birthPlaceCoordinates): Grave
    {
        $this->birthPlaceCoordinates = $birthPlaceCoordinates;
        return $this;
    }

    public function getDeathPlaceCoordinates(): ?string
    {
        return $this->deathPlaceCoordinates;
    }

    public function setDeathPlaceCoordinates(?string $deathPlaceCoordinates): Grave
    {
        $this->deathPlaceCoordinates = $deathPlaceCoordinates;
        return $this;
    }

    public function getGravePlace(): ?string
    {
        return $this->gravePlace;
    }

    public function setGravePlace(?string $gravePlace): Grave
    {
        $this->gravePlace = $gravePlace;
        return $this;
    }

    public function getGravePlaceCoordinates(): ?string
    {
        return $this->gravePlaceCoordinates;
    }

    public function setGravePlaceCoordinates(?string $gravePlaceCoordinates): Grave
    {
        $this->gravePlaceCoordinates = $gravePlaceCoordinates;
        return $this;
    }

    public function getEpitaph(): ?string
    {
        return $this->epitaph;
    }

    public function setEpitaph(?string $epitaph): Grave
    {
        $this->epitaph = $epitaph;
        return $this;
    }

    public function getGender(): int
    {
        return $this->gender;
    }

    public function setGender(int $gender): Grave
    {
        $this->gender = $gender;
        return $this;
    }

    public function getBiography(): ?string
    {
        return $this->biography;
    }

    public function setBiography(?string $biography): Grave
    {
        $this->biography = $biography;
        return $this;
    }

    /**
     * @return Collection<int, PersonPhoto>
     */
    public function getPersonPhotos(): Collection
    {
        return $this->personPhotos;
    }

    public function addPersonPhoto(PersonPhoto $personPhoto): static
    {
        if (!$this->personPhotos->contains($personPhoto)) {
            $this->personPhotos->add($personPhoto);
            $personPhoto->setGrave($this);
        }

        return $this;
    }

    public function removePersonPhoto(PersonPhoto $personPhoto): static
    {
        if ($this->personPhotos->removeElement($personPhoto)) {
            // set the owning side to null (unless already changed)
            if ($personPhoto->getGrave() === $this) {
                $personPhoto->setGrave(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Flower>
     */
    public function getFlowers(): Collection
    {
        return $this->flowers;
    }

    public function addFlower(Flower $flower): static
    {
        if (!$this->flowers->contains($flower)) {
            $this->flowers->add($flower);
            $flower->setGrave($this);
        }

        return $this;
    }

    public function removeFlower(Flower $flower): static
    {
        if ($this->flowers->removeElement($flower)) {
            // set the owning side to null (unless already changed)
            if ($flower->getGrave() === $this) {
                $flower->setGrave(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Condolence>
     */
    public function getCondolences(): Collection
    {
        return $this->condolences;
    }

    public function addCondolence(Condolence $condolence): static
    {
        if (!$this->condolences->contains($condolence)) {
            $this->condolences->add($condolence);
            $condolence->setGrave($this);
        }

        return $this;
    }

    public function removeCondolence(Condolence $condolence): static
    {
        if ($this->condolences->removeElement($condolence)) {
            // set the owning side to null (unless already changed)
            if ($condolence->getGrave() === $this) {
                $condolence->setGrave(null);
            }
        }

        return $this;
    }
    
}
