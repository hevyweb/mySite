<?php

declare(strict_types=1);

namespace App\Tests\Integration\Command;

use App\Command\DeploymentCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class DeploymentCommandTest extends KernelTestCase
{
    private ?CommandTester $commandTester = null;
    private ?Application $application = null;

    protected function setUp(): void
    {
        $kernel = $this->bootKernel();

        $this->application = new Application($kernel);
        $this->application->add(new DeploymentCommand());

        $this->commandTester = new CommandTester(
            $this->application->find('app:run-sequential-commands')
        );
    }

    protected function tearDown(): void
    {
        $this->commandTester = null;
        $this->application = null;

        parent::tearDown();
    }

    public function testExecuteSuccessfully(): void
    {
        $this->commandTester->execute([]);

        $this->assertSame(Command::SUCCESS, $this->commandTester->getStatusCode());

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('The deployment sequence completed successfully', $output);
    }
}
