<?php
namespace App\Entity;

use App\Repository\StatRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StatRepository::class)]
#[ORM\Table(name: 'stats')]
#[ORM\Index(columns: ['path', 'created_at'], name: 'idx_path_date')]
#[ORM\Index(columns: ['visitor_hash'], name: 'idx_visitor_hash')]
class Stat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $path = null;

    #[ORM\Column(length: 64)]
    private ?string $visitorHash = null; // Daily unique user fingerprint

    // --- Device & Environment ---
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $deviceType = null; // mobile, tablet, desktop, bot

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $browser = null; // e.g., Chrome, Safari, Firefox

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $os = null; // e.g., Windows, macOS, Android, iOS

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $language = null; // Primary language (e.g., 'en', 'uk')

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $country = null; // Country code (e.g., 'UA', 'US')

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $city = null; // Optional: Extracted via GeoIP database

    // --- Traffic Attribution ---
    #[ORM\Column(length: 500, nullable: true)]
    private ?string $referer = null; // External or internal source URL

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $utmSource = null; // e.g., google, newsletter, telegram

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $utmMedium = null; // e.g., cpc, banner, email

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $utmCampaign = null; // e.g., spring_sale

    // --- Technical Request Details ---
    #[ORM\Column(length: 45, nullable: true)]
    private ?string $ipAddress = null; // Stored anonymized if privacy is a concern

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $userAgent = null; // Raw string (optional to store if parsed cleanly)

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private int $hits = 1;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    // --- Getters and Setters ---

    public function getId(): ?int { return $this->id; }

    public function getPath(): ?string { return $this->path; }
    public function setPath(string $path): static { $this->path = $path; return $this; }

    public function getVisitorHash(): ?string { return $this->visitorHash; }
    public function setVisitorHash(string $visitorHash): static { $this->visitorHash = $visitorHash; return $this; }

    public function getDeviceType(): ?string { return $this->deviceType; }
    public function setDeviceType(?string $deviceType): static { $this->deviceType = $deviceType; return $this; }

    public function getBrowser(): ?string { return $this->browser; }
    public function setBrowser(?string $browser): static { $this->browser = $browser; return $this; }

    public function getOs(): ?string { return $this->os; }
    public function setOs(?string $os): static { $this->os = $os; return $this; }

    public function getLanguage(): ?string { return $this->language; }
    public function setLanguage(?string $language): static { $this->language = $language; return $this; }

    public function getCountry(): ?string { return $this->country; }
    public function setCountry(?string $country): static { $this->country = $country; return $this; }

    public function getCity(): ?string { return $this->city; }
    public function setCity(?string $city): static { $this->city = $city; return $this; }

    public function getReferer(): ?string { return $this->referer; }
    public function setReferer(?string $referer): static { $this->referer = $referer; return $this; }

    public function getUtmSource(): ?string { return $this->utmSource; }
    public function setUtmSource(?string $utmSource): static { $this->utmSource = $utmSource; return $this; }

    public function getUtmMedium(): ?string { return $this->utmMedium; }
    public function setUtmMedium(?string $utmMedium): static { $this->utmMedium = $utmMedium; return $this; }

    public function getUtmCampaign(): ?string { return $this->utmCampaign; }
    public function setUtmCampaign(?string $utmCampaign): static { $this->utmCampaign = $utmCampaign; return $this; }

    public function getIpAddress(): ?string { return $this->ipAddress; }
    public function setIpAddress(?string $ipAddress): static { $this->ipAddress = $ipAddress; return $this; }

    public function getUserAgent(): ?string { return $this->userAgent; }
    public function setUserAgent(?string $userAgent): static { $this->userAgent = $userAgent; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->created_at ?? $this->createdAt; }

    public function getHits(): int { return $this->hits; }
    public function setHits(int $hits): static { $this->hits = $hits; return $this; }
}