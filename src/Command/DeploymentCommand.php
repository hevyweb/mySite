<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'app:run-sequential-commands',
    description: 'Runs deployment commands in sequence',
)]
class DeploymentCommand extends Command
{
    protected static $defaultName = 'app:run-sequential-commands';

    #[\Override]
    protected function configure(): void
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Runs deployment commands in sequence')
            ->addOption(
                'dry-run',
                'd',
                InputOption::VALUE_NONE,
                'Simulate the deployment process without executing changes'
            );
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $isDryRun = $input->getOption('dry-run');

        if ($isDryRun) {
            $io->note('Dry run mode enabled. No changes will actually be made.');
            $io->writeln('<info>[DRY RUN] Would run composer install.</info>');
        } else {
            $composer = new Process(['composer', 'install']);
            $composer->run();
            if (!$composer->isSuccessful()) {
                $io->writeln('<error>An error occurred: '.$composer->getErrorOutput().'</error>');

                return Command::FAILURE;
            }
            $io->writeln('<info>Composer install executed.</info>');
        }

        foreach ($this->getCommandList() as $commandLine) {
            try {
                $command = $this->getApplication()->find($commandLine);
                if (self::SUCCESS !== $command->run($input, $output)) {
                    throw new \Exception('Command execution failed. '.$command->getName());
                }

                if (!$isDryRun) {
                    $io->writeln('<info>'.$command->getName().' executed.</info>');
                }
            } catch (\Exception $e) {
                $io->writeln('<error>An error occurred: '.$e->getMessage().'</error>');

                return Command::FAILURE;
            }
        }

        $io->writeln('<info>The deployment sequence completed successfully</info>');

        return Command::SUCCESS;
    }

    /**
     * @return string[]
     */
    private function getCommandList(): array
    {
        return [
            RemoveUnusedFilesCommand::$defaultName,
            UnzipAssetsCommand::$defaultName,
        ];
    }
}