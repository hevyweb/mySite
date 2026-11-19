<?php

namespace App\Tests\Unit\Service;

use App\Entity\Stat;
use App\Service\StatsParser;
use PHPUnit\Framework\TestCase;

class StatsParserTest extends TestCase
{
    private StatsParser $statsParser;

    protected function setUp(): void
    {
        $this->statsParser = new StatsParser();
    }

    public function testParseEmptyStats(): void
    {
        $result = $this->statsParser->parse([]);

        $this->assertSame([
            'total' => [],
            'today' => [],
        ], $result);
    }

    public function testParseGroupsAndDeduplicatesVisitorsCorrectly(): void
    {
        $today = new \DateTimeImmutable();
        $yesterday = new \DateTimeImmutable('-1 day');

        $stat1 = $this->createMock(Stat::class);
        $stat1->method('getPath')->willReturn('/graves/123');
        $stat1->method('getVisitorHash')->willReturn('visitor_a');
        $stat1->method('getCreatedAt')->willReturn($today);

        $stat2 = $this->createMock(Stat::class);
        $stat2->method('getPath')->willReturn('/graves/123');
        $stat2->method('getVisitorHash')->willReturn('visitor_a');
        $stat2->method('getCreatedAt')->willReturn($today);

        $stat3 = $this->createMock(Stat::class);
        $stat3->method('getPath')->willReturn('/graves/123');
        $stat3->method('getVisitorHash')->willReturn('visitor_b');
        $stat3->method('getCreatedAt')->willReturn($today);

        $stat4 = $this->createMock(Stat::class);
        $stat4->method('getPath')->willReturn('/graves/123');
        $stat4->method('getVisitorHash')->willReturn('visitor_a');
        $stat4->method('getCreatedAt')->willReturn($yesterday);

        $stat5 = $this->createMock(Stat::class);
        $stat5->method('getPath')->willReturn('/graves/456');
        $stat5->method('getVisitorHash')->willReturn('visitor_c');
        $stat5->method('getCreatedAt')->willReturn($yesterday);

        $result = $this->statsParser->parse([$stat1, $stat2, $stat3, $stat4, $stat5]);

        $this->assertEquals([
            'total' => [
                '/123' => 1,
                '/456' => 1,
            ],
            'today' => [
                '/123' => 2,
            ],
        ], $result);
    }
}
