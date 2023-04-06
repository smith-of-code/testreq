<?php

namespace QSoft\Foundation\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class CreateProjectFinalize extends Command
{
    protected function configure()
    {
        $this
            ->setName('bitrix:configure-project-finalize')
            ->setDescription('Complete setup bitrix framework')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $finder = new Finder();

        if ($finder->in('./')->files()->name('create-project.lock')->count() == 0) {
            $output->writeln('<comment>Project is not configured. Please, run `php disposer bitrix:configure-project`</comment>');
            exit(0);
        }

        if ($finder->notName('.gitkeep')->in('app/core/bitrix')->directories()->count() == 0) {
            $output->writeln('<comment>Bitrix framework not installed. Please, go to http://your_host_name/bitrixsetup.php</comment>');
            exit(0);
        }
    }
}