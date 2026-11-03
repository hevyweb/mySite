<?php

namespace App\Service;

use App\Entity\Stat;
use App\Repository\StatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class StatsTracker
{
    private const BOT_PATTERNS = '/(bot|crawler|spider|crawling|slurp|adsbot|googlebot|bingbot|yandex|baidu|duckduckbot|facebookexternalhit|meta-externalagent|ia_archiver)/i';

    private const EXCLUDED_PREFIXES = ['/_', '/css', '/js', '/api'];
    private const EXCLUDED_SUFFIXES = ['serviceWorker.js', 'manifest.json'];
    private const ASSET_PATTERN = '/\.(jpg|jpeg|png|gif|webp|svg|ico|avif|json|xml|txt)$/i';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly StatRepository $statsRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function track(Request $request): void
    {
        if ($this->shouldSkipTracking($request)) {
            return;
        }

        // Prevent trying to use a closed EntityManager
        if (!$this->entityManager->isOpen()) {
            return;
        }

        try {
            $path = $request->getPathInfo();
            $userAgent = $request->headers->get('User-Agent', '');
            $visitorHash = $this->generateVisitorHash($request, $userAgent);

            $stat = $this->statsRepository->findTodayVisit($path, $visitorHash);

            if ($stat) {
                $stat->setHits($stat->getHits() + 1);
            } else {
                $stat = $this->createNewStat($request, $path, $userAgent, $visitorHash);
                $this->entityManager->persist($stat);
            }

            $this->entityManager->flush();
        } catch (\Throwable $e) {
            $this->logger->error('Error tracking stats', ['exception' => $e]);
        }
    }

    private function shouldSkipTracking(Request $request): bool
    {
        $path = $request->getPathInfo();

        foreach (self::EXCLUDED_PREFIXES as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return true;
            }
        }

        foreach (self::EXCLUDED_SUFFIXES as $suffix) {
            if (str_ends_with($path, $suffix)) {
                return true;
            }
        }

        if (preg_match(self::ASSET_PATTERN, $path)) {
            return true;
        }

        $userAgent = $request->headers->get('User-Agent', '');
        if (empty($userAgent) || preg_match(self::BOT_PATTERNS, $userAgent)) {
            return true;
        }

        return false;
    }

    private function generateVisitorHash(Request $request, string $userAgent): string
    {
        $clientIp = $request->getClientIp() ?? '0.0.0.0';
        $dailySalt = (new \DateTimeImmutable())->format('Y-m-d');

        return hash('sha256', $clientIp.$userAgent.$dailySalt);
    }

    private function createNewStat(Request $request, string $path, string $userAgent, string $visitorHash): Stat
    {
        [$browser, $os, $deviceType] = $this->parseUserAgent($userAgent);

        $acceptLang = $request->headers->get('Accept-Language');
        $lang = $acceptLang ? strtolower(substr($acceptLang, 0, 2)) : 'unknown';
        $country = $request->headers->get('CF-IPCountry', 'unknown');

        $referer = $request->headers->get('referer');
        $host = $request->getHost();
        if ($referer && str_contains($referer, $host)) {
            $referer = 'internal';
        }

        $query = $request->query;

        $stat = new Stat();
        $stat->setPath($path);
        $stat->setUserAgent($userAgent);
        $stat->setIpAddress($request->getClientIp());
        $stat->setBrowser($browser);
        $stat->setOs($os);
        $stat->setDeviceType($deviceType);
        $stat->setVisitorHash($visitorHash);
        $stat->setLanguage($lang);
        $stat->setCountry($country);
        $stat->setReferer($referer);
        $stat->setUtmSource($query->get('utm_source'));
        $stat->setUtmMedium($query->get('utm_medium'));
        $stat->setUtmCampaign($query->get('utm_campaign'));
        $stat->setHits(1);

        return $stat;
    }

    /**
     * @return array{0: string, 1: string, 2: string}
     */
    private function parseUserAgent(string $ua): array
    {
        $browser = 'Unknown';
        $os = 'Unknown';
        $deviceType = 'Desktop';

        if (preg_match('/mobi|android|iphone|ipad|ipod/i', $ua)) {
            $deviceType = str_contains(strtolower($ua), 'ipad') ? 'Tablet' : 'Mobile';
        }

        if (preg_match('/windows nt 10/i', $ua)) {
            $os = 'Windows 10/11';
        } elseif (preg_match('/windows/i', $ua)) {
            $os = 'Windows';
        } elseif (preg_match('/mac os x/i', $ua)) {
            $os = 'macOS';
        } elseif (preg_match('/android/i', $ua)) {
            $os = 'Android';
        } elseif (preg_match('/iphone|ipad|ipod/i', $ua)) {
            $os = 'iOS';
        } elseif (preg_match('/linux/i', $ua)) {
            $os = 'Linux';
        }

        if (preg_match('/edg/i', $ua)) {
            $browser = 'Edge';
        } elseif (preg_match('/chrome/i', $ua)) {
            $browser = 'Chrome';
        } elseif (preg_match('/firefox/i', $ua)) {
            $browser = 'Firefox';
        } elseif (preg_match('/safari/i', $ua)) {
            $browser = 'Safari';
        } elseif (preg_match('/opr|opera/i', $ua)) {
            $browser = 'Opera';
        }

        return [$browser, $os, $deviceType];
    }
}
