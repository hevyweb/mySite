<?php

namespace App\Tests\Integration\Command;

use App\Command\UnzipAssetsCommand;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class UnzipAssetsCommandTest extends KernelTestCase
{
    private ?CommandTester $commandTester;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $command = $container->get(UnzipAssetsCommand::class);
        $this->commandTester = new CommandTester($command);
    }

    protected function tearDown(): void
    {
        $this->commandTester = null;
        parent::tearDown();
    }

    public function testExecuteSuccessfully(): void
    {
        $this->commandTester->execute([]);

        $this->assertSame(Command::SUCCESS, $this->commandTester->getStatusCode());

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Unzipped public/assets.zip to public', $output);
    }
}
