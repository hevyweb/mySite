<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class UnzipAssetsCommand extends Command
{
    public static $defaultName = 'app:unzip-assets';

    protected function configure()
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Unzips public/assets.zip to the public directory')
            ->setHelp('This command allows you to unzip public/assets.zip to the public directory');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filesystem = new Filesystem();

        $zipFile = 'public/assets.zip';
        $destination = 'public';

        if (!$filesystem->exists($zipFile)) {
            $io->writeln("<error>The file $zipFile does not exist</error>");
            return Command::FAILURE;
        }

        if ($filesystem->exists($destination)) {
            $io->writeln("<info>The directory $destination already exists</info>");
        }

        try {
            $process = new Process(['unzip', $zipFile, '-d', $destination]);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $io->writeln("<info>Unzipped $zipFile to $destination</info>");
        } catch (ProcessFailedException $e) {
            $io->writeln("<error>Failed to unzip $zipFile: " . $e->getMessage() . "</error>");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
