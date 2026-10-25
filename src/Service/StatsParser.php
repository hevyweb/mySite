<?php

namespace App\Service;
use App\Entity\Stat;

class StatsParser
{
    /**
     * @param Stat[] $stats
     * @return array
     */
    public function parse(array $stats): array
    {
        $total = [];
        $today = [];
        foreach ($stats as $stat) {
            $graveId = substr($stat->getPath(), 7);
            if ($stat->getCreatedAt()->format('Y-m-d') === (new \DateTimeImmutable())->format('Y-m-d')) {
                $today[$graveId][$stat->getVisitorHash()] = 1;
            } else {
                $total[$graveId][$stat->getVisitorHash()] = 1;
            }
            
        }
        
        foreach ($total as &$grave) {
            $grave = array_sum($grave);
        }
        
        foreach ($today as &$grave) {
            $grave = array_sum($grave);
        }
        
        return [
            'total' => $total,
            'today' => $today,
        ];
    }
}