<?php

namespace App\Command;

use App\Service\CheckMessages;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:check-messages',
    description: 'This command checks messages and sends and email if any.',
)]
class CheckMessagesCommand extends Command
{
    public function __construct(private CheckMessages $checkMessages)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->checkMessages->checkAndNotify();

        $output->writeln('Executes!');

        return Command::SUCCESS;
    }
}
