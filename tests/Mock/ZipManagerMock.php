<?php

namespace App\Tests\Mock;

use App\Service\FileSystem\ZipManagerInterface;

class ZipManagerMock implements ZipManagerInterface
{
    #[\Override]
    public function zip(string $sourcePath, string $destinationZipPath): void
    {
        echo "Zipping $sourcePath to $destinationZipPath\n";
    }

    #[\Override]
    public function unzip(string $zipFilePath, string $destinationPath): void
    {
        echo "Unzipping $zipFilePath to $destinationPath\n";
    }
}