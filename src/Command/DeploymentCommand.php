<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

class DeploymentCommand extends Command
{
    protected static string $defaultName = 'app:run-sequential-commands';

    #[\Override]
    protected function configure()
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Runs deployment commands in sequence');
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $composer = new Process(['composer', 'install']);
        $composer->run();
        if (!$composer->isSuccessful()) {
            $io->writeln('<error>An error occurred: '.$composer->getErrorOutput().'</error>');

            return Command::FAILURE;
        }
        $io->writeln('<info>Composer install executed.</info>');

        foreach ($this->getCommandList() as $commandLine) {
            try {
                $command = $this->getApplication()->find($commandLine);
                if (self::SUCCESS !== $command->run($input, $output)) {
                    throw new \Exception('Command execution failed. '.$command->getName());
                }
                $io->writeln('<info>'.$command->getName().' executed.</info>');
            } catch (\Exception $e) {
                $io->writeln('<error>An error occurred: '.$e->getMessage().'</error>');

                return Command::FAILURE;
            }
        }

        $io->writeln('<info>The second command succeeded</info>');

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
