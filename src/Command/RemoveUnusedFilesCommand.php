<?php

namespace App\Command;

use App\Service\FileSystem\FileManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:remove-unused-files',
    description: 'Removes all files that shouldn\'t exist on production',
)]
class RemoveUnusedFilesCommand extends Command
{
    public static $defaultName = 'app:remove-unused-files';
    
    public function __construct(
        private readonly FileManagerInterface $fileManager,
    ){
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Removes all files that shouldn\'t exist on production');
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Define a list of files or directories that should not exist on production
        $unusedFiles = [
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
            try {
                //$this->fileManager->remove($file);
                $io->writeln("<info>Removed $file</info>");
            } catch (\Exception $e) {
                $io->writeln("<error>Failed to remove $file: ".$e->getMessage().'</error>');
            }            
        }

        return Command::SUCCESS;
    }
}