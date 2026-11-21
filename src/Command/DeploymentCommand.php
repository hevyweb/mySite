<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:run-sequential-commands',
    description: 'Runs deployment commands in sequence',
)]
class DeploymentCommand extends Command
{
    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

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

        $io->writeln('<info>The deployment sequence completed successfully</info>');

        return Command::SUCCESS;
    }

    /**
     * @return string[]
     */
    private function getCommandList(): array
    {
        return [
            RemoveUnusedFilesCommand::NAME,
            UnzipAssetsCommand::NAME,
        ];
    }
}
