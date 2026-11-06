<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

class RemoveUnusedFilesCommand extends Command
{
    public static $defaultName = 'app:remove-unused-files';

    protected function configure()
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Removes all files that shouldn\'t exist on production')
            ->setHelp('This command allows you to remove all files that shouldn\'t exist on production');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filesystem = new Filesystem();

        // Define a list of files or directories that should not exist on production
        $unusedFiles = [
            // Example files or directorie
            '.github',
            'assets',
            'bin/csfixer',
            'bin/phpunit',
            'config/packages/dev',
            'config/packages/test',
            'config/routes/dev',
            'docker',
            'tests',
            '.cpanel.yml',
            '.env.dist',
            '.env.test',
            '.php-cs-fixer.php',
            'docker-compose.yml',
            'package.json',
            'phpstan.neon',
            'phpunit.xml.dist',
            'psalm.xml',
            'README.md',
            'yarn.lock',
            'public/assets',
        ];

        foreach ($unusedFiles as $file) {
            if ($filesystem->exists($file)) {
                try {
                    $filesystem->remove($file);
                    $io->writeln("<info>Removed $file</info>");
                } catch (\Exception $e) {
                    $io->writeln("<error>Failed to remove $file: ".$e->getMessage().'</error>');
                }
            } else {
                $io->writeln("<comment>$file does not exist</comment>");
            }
        }

        return Command::SUCCESS;
    }
}
