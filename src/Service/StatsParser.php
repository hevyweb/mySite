<?php

namespace App\Service;

use App\Entity\Stat;

class StatsParser
{
    /**
     * @param Stat[] $stats
     *
     * @return array{total: array<string, int>, today: array<string, int>}
     */
    public function parse(array $stats): array
    {
        /** @var array<string, array<string, true>> $totalVisitors */
        $totalVisitors = [];
        /** @var array<string, array<string, true>> $todayVisitors */
        $todayVisitors = [];

        $todayDate = (new \DateTimeImmutable())->format('Y-m-d');

        foreach ($stats as $stat) {
            $graveId = substr($stat->getPath(), 7);
            $visitorHash = $stat->getVisitorHash();

            if ($stat->getCreatedAt()->format('Y-m-d') === $todayDate) {
                $todayVisitors[$graveId][$visitorHash] = true;
            } else {
                $totalVisitors[$graveId][$visitorHash] = true;
            }
        }

        $total = [];
        foreach ($totalVisitors as $graveId => $visitors) {
            $total[$graveId] = count($visitors);
        }

        $today = [];
        foreach ($todayVisitors as $graveId => $visitors) {
            $today[$graveId] = count($visitors);
        }

        return [
            'total' => $total,
            'today' => $today,
        ];
    }
}
