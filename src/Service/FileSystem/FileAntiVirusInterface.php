<?php

namespace App\Service\FileSystem;

interface FileAntiVirusInterface
{
    public function sanitize(string $fileName, string $destination): void;
}
