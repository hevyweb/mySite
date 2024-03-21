<?php

namespace App\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class TagDataTransformer implements DataTransformerInterface
{
    public function transform(mixed $value): string
    {
        if (!empty($value)) {
            return implode(', ', $value);
        }

        return '';
    }

    public function reverseTransform(mixed $value): array
    {
        if (!empty($value)) {
            return array_map('trim', explode(',', $value));
        }

        return [];
    }
}
