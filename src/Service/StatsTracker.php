<?php

namespace App\Service;

use App\Entity\Stat;
use App\Repository\StatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class StatsTracker
{
    private const BOT_PATTERNS = '/(bot|crawler|spider|crawling|slurp|adsbot|googlebot|bingbot|yandex|baidu|duckduckbot|facebookexternalhit|meta-externalagent|ia_archiver)/i';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private StatRepository $statsRepository
    ) {}

    public function track(Request $request): void
    {
        $path = $request->getPathInfo();

        if (
            str_starts_with($path, '/_') ||
            str_starts_with($path, '/css') ||
            str_starts_with($path, '/js') ||
            str_starts_with($path, '/api') || // Catch standard API routes
            str_ends_with($path, 'serviceWorker.js') ||
            str_ends_with($path, 'manifest.json') ||
            preg_match('/\.(jpg|jpeg|png|gif|webp|svg|ico|avif|json|xml|txt)$/i', $path)
        ) {
            return;
        }

        $userAgent = $request->headers->get('User-Agent', '');
        if (empty($userAgent) || preg_match(self::BOT_PATTERNS, $userAgent)) {
            return;
        }

        $clientIp = $request->getClientIp();
        $dailySalt = (new \DateTimeImmutable())->format('Y-m-d');
        $visitorHash = hash('sha256', $clientIp . $userAgent . $dailySalt);

        // Check if this visitor already logged a hit for this path today
        $stats = $this->statsRepository->findTodayVisit($path, $visitorHash);

        if ($stats) {
            // User already visited today: just increment the hit count
            $stats->setHits($stats->getHits() + 1);
        } else {
            // First visit today: create a fresh record
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

            $stats = new Stat();
            $stats->setPath($path);
            $stats->setUserAgent($userAgent);
            $stats->setIpAddress($clientIp);
            $stats->setBrowser($browser);
            $stats->setOs($os);
            $stats->setDeviceType($deviceType);
            $stats->setVisitorHash($visitorHash);
            $stats->setLanguage($lang);
            $stats->setCountry($country);
            $stats->setReferer($referer);
            $stats->setUtmSource($query->get('utm_source'));
            $stats->setUtmMedium($query->get('utm_medium'));
            $stats->setUtmCampaign($query->get('utm_campaign'));
            $stats->setHits(1);

            $this->entityManager->persist($stats);
        }

        $this->entityManager->flush();
    }

    private function parseUserAgent(string $ua): array
    {
        $browser = 'Unknown';
        $os = 'Unknown';
        $deviceType = 'Desktop';

        if (preg_match('/mobi|android|iphone|ipad|ipod/i', $ua)) {
            $deviceType = str_contains(strtolower($ua), 'ipad') ? 'Tablet' : 'Mobile';
        }

        if (preg_match('/windows nt 10/i', $ua)) { $os = 'Windows 10/11'; }
        elseif (preg_match('/windows/i', $ua)) { $os = 'Windows'; }
        elseif (preg_match('/mac os x/i', $ua)) { $os = 'macOS'; }
        elseif (preg_match('/android/i', $ua)) { $os = 'Android'; }
        elseif (preg_match('/iphone|ipad|ipod/i', $ua)) { $os = 'iOS'; }
        elseif (preg_match('/linux/i', $ua)) { $os = 'Linux'; }

        if (preg_match('/edg/i', $ua)) { $browser = 'Edge'; }
        elseif (preg_match('/chrome/i', $ua)) { $browser = 'Chrome'; }
        elseif (preg_match('/firefox/i', $ua)) { $browser = 'Firefox'; }
        elseif (preg_match('/safari/i', $ua)) { $browser = 'Safari'; }
        elseif (preg_match('/opr|opera/i', $ua)) { $browser = 'Opera'; }

        return [$browser, $os, $deviceType];
    }
}