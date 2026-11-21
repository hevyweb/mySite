<?php

namespace App\Service\FileSystem;

interface FileAntiVirusInterface
{
    /**
     * @psalm-suppress PossiblyUnusedMethod
     *
     * @TODO this method is not used yet. I will fix it later to find some better solution.
     */
    public function sanitize(string $fileName, string $destination): bool;
}
