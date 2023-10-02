<?php

namespace App\Service;

class Strings
{
    public function generateRandomString(int $length = 12): string
    {
        $charset = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*-_+=?';
        return $this->stringGenerate($length, $charset);

    }

    public function generateRandomSlug(int $length = 12): string
    {
        $charset = '0123456789abcdefghijklmnopqrstuvwxyz-';
        return $this->stringGenerate($length, $charset);
    }

    private function stringGenerate(int $length, string $charset): string
    {
        $charactersLength = strlen($charset) - 1;
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $charset[mt_rand(0, $charactersLength)];
        }
        return $randomString;
    }
}