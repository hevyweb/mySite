<?php

namespace App\Command;

use App\Service\FileSystem\ZipManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:unzip-assets',
    description: 'Unzips public/assets.zip to the public directory',
)]
class UnzipAssetsCommand extends Command
{
    public const ZIP_FILE = 'public/assets.zip';
    public const DESTINATION = 'public';

    public const string NAME = 'app:unzip-assets';

    public function __construct(
        private readonly ZipManagerInterface $zipManager,
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Unzips public/assets.zip to the public directory')
            ->setHelp('This command allows you to unzip public/assets.zip to the public directory');
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->zipManager->unzip(self::ZIP_FILE, self::DESTINATION);

            $io->writeln('<info>Unzipped '.self::ZIP_FILE.' to '.self::DESTINATION.'</info>');
        } catch (\Exception $e) {
            $io->writeln('<error>Failed to unzip '.self::ZIP_FILE.': '.$e->getMessage().'</error>');

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
