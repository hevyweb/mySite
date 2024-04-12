<?php

namespace App\Service;

class ArrayService
{
    /**
     * @return array<int>
     */
    public function getIntegerIds(mixed $requestVariable): array
    {
        if (!is_array($requestVariable)) {
            return [];
        }

        if (!count($requestVariable)) {
            return [];
        }

        return array_unique(array_map('intval', array_keys($requestVariable)));
    }
}
