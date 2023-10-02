<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

class ArrayService
{
    public function getIntegerIds($requestVariable): array
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