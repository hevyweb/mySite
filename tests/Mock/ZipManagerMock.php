<?php

namespace App\Tests\Mock;

use App\Service\FileSystem\ZipManagerInterface;
use Psr\Log\LoggerInterface;

class ZipManagerMock implements ZipManagerInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    #[\Override]
    public function zip(string $sourcePath, string $destinationZipPath): void
    {
        $this->logger->debug("Zipping $sourcePath to $destinationZipPath");
    }

    #[\Override]
    public function unzip(string $zipFilePath, string $destinationPath): void
    {
        $this->logger->debug("Unzipping $zipFilePath to $destinationPath");
    }
}
