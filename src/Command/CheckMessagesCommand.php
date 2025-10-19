<?php

namespace App\Command;

use App\Service\CheckMessages;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:check-messages',
    description: 'This command checks messages and sends and email if any.',
)]
class CheckMessagesCommand extends Command
{
    public function __construct(private readonly CheckMessages $checkMessages)
    {
        parent::__construct();
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->checkMessages->checkAndNotify();

        $output->writeln('Executes!');

        return Command::SUCCESS;
    }
}
