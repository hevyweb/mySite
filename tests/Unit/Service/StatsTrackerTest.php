<?php

namespace App\Tests\Unit\Service;

use App\Entity\Stat;
use App\Repository\StatRepository;
use App\Service\StatsTracker;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class StatsTrackerTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private StatRepository $statsRepository;
    private LoggerInterface $logger;
    private StatsTracker $statsTracker;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->statsRepository = $this->createMock(StatRepository::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->statsTracker = new StatsTracker(
            $this->entityManager,
            $this->statsRepository,
            $this->logger
        );
    }

    #[DataProvider('skippedRequestsDataProvider')]
    public function testTrackSkipsTrackingUnderCertainConditions(Request $request): void
    {
        $this->entityManager->expects($this->never())->method('isOpen');
        $this->statsRepository->expects($this->never())->method('findTodayVisit');

        $this->statsTracker->track($request);
    }

    public static function skippedRequestsDataProvider(): \Generator
    {
        yield 'excluded prefix /_wdt' => [
            Request::create('/_wdt/some-token'),
        ];

        yield 'excluded suffix serviceWorker.js' => [
            Request::create('/serviceWorker.js'),
        ];

        yield 'asset extension jpg' => [
            Request::create('/images/photo.jpg'),
        ];

        yield 'empty user agent' => [
            (static function () {
                $req = Request::create('/');
                $req->headers->set('User-Agent', '');

                return $req;
            })(),
        ];

        yield 'bot user agent' => [
            (static function () {
                $req = Request::create('/');
                $req->headers->set('User-Agent', 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)');

                return $req;
            })(),
        ];
    }

    public function testTrackStopsWhenEntityManagerIsClosed(): void
    {
        $request = Request::create('/about', 'GET', [], [], [], ['HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)']);

        $this->entityManager->expects($this->once())
            ->method('isOpen')
            ->willReturn(false);

        $this->statsRepository->expects($this->never())->method('findTodayVisit');

        $this->statsTracker->track($request);
    }

    public function testTrackIncrementsHitsWhenVisitExistsToday(): void
    {
        $request = Request::create('/about', 'GET', [], [], [], [
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0',
        ]);

        $this->entityManager->expects($this->once())
            ->method('isOpen')
            ->willReturn(true);

        $existingStat = $this->createMock(Stat::class);
        $existingStat->expects($this->once())->method('getHits')->willReturn(1);
        $existingStat->expects($this->once())->method('setHits')->with(2);

        $this->statsRepository->expects($this->once())
            ->method('findTodayVisit')
            ->willReturn($existingStat);

        $this->entityManager->expects($this->never())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $this->statsTracker->track($request);
    }

    #[DataProvider('userAgentDataProvider')]
    public function testTrackUserAgentParsing(
        string $userAgent,
        string $expectedOs,
        string $expectedBrowser,
        string $expectedDeviceType,
    ): void {
        $request = Request::create('/about', 'GET', [], [], [], [
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_USER_AGENT' => $userAgent,
        ]);
        $request->headers->set('CF-IPCountry', 'US');

        $this->entityManager->expects($this->once())
            ->method('isOpen')
            ->willReturn(true);

        $this->statsRepository->expects($this->once())
            ->method('findTodayVisit')
            ->willReturn(null);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function (Stat $stat) use ($expectedOs, $expectedBrowser, $expectedDeviceType) {
                return '/about' === $stat->getPath()
                    && $stat->getOs() === $expectedOs
                    && $stat->getBrowser() === $expectedBrowser
                    && $stat->getDeviceType() === $expectedDeviceType
                    && 1 === $stat->getHits();
            }));

        $this->entityManager->expects($this->once())->method('flush');

        $this->statsTracker->track($request);
    }

    public static function userAgentDataProvider(): \Generator
    {
        yield 'Windows 10/11 Chrome' => [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Windows 10/11',
            'Chrome',
            'Desktop',
        ];

        yield 'Windows Edge' => [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 Edg/120.0.2210.91',
            'Windows 10/11',
            'Edge',
            'Desktop',
        ];

        yield 'Windows 7 Firefox' => [
            'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:109.0) Gecko/20100101 Firefox/115.0',
            'Windows',
            'Firefox',
            'Desktop',
        ];

        yield 'Windows Opera' => [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36 OPR/96.0.4693.50',
            'Windows 10/11',
            'Opera',
            'Desktop',
        ];

        yield 'macOS Safari' => [
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Safari/605.1.15',
            'macOS',
            'Safari',
            'Desktop',
        ];

        yield 'Android Mobile Chrome' => [
            'Mozilla/5.0 (Linux; Android 13; SM-S911B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36',
            'Android',
            'Chrome',
            'Mobile',
        ];

        yield 'Linux Firefox' => [
            'Mozilla/5.0 (X11; Linux x86_64; rv:109.0) Gecko/20100101 Firefox/115.0',
            'Linux',
            'Firefox',
            'Desktop',
        ];

        yield 'iPad Tablet Safari' => [
            'Mozilla/5.0 (iPad; CPU OS 16_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Mobile/15E148 Safari/604.1',
            'macOS',
            'Safari',
            'Tablet',
        ];

        yield 'Unknown / Default UA' => [
            'CustomApp/1.0',
            'Unknown',
            'Unknown',
            'Desktop',
        ];
    }

    public function testTrackLogsExceptionOnFailure(): void
    {
        $request = Request::create('/about', 'GET', [], [], [], [
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        ]);

        $this->entityManager->expects($this->once())
            ->method('isOpen')
            ->willReturn(true);

        $exception = new \RuntimeException('Database error');
        $this->statsRepository->expects($this->once())
            ->method('findTodayVisit')
            ->willThrowException($exception);

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Error tracking stats', ['exception' => $exception]);

        $this->statsTracker->track($request);
    }
}
