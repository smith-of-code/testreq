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

class CreateSite extends Command
{
    protected function configure()
    {
        $this
            ->setName('bitrix:create-site')
            ->setDescription('Run setup utility for bitrix framework')
            ->addOption('first', null, InputOption::VALUE_NONE, 'Добавляет скрипт установки Bitrix в корень сайта')
            ->addArgument('site-folder', InputArgument::OPTIONAL, 'Символьный код сайта (Например: s1)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projectSiteFolder = $input->getArgument('site-folder');

        $helper = $this->getHelper('question');

        while (!$projectSiteFolder) {
            $projectSiteFolderQuestion = new Question('What is you default project site folder (Ex. yaposha.ru) ? ', false);
            $projectSiteFolder = $helper->ask($input, $output, $projectSiteFolderQuestion);
        }

        $finder = new Finder();

        if ($finder->directories()->name($projectSiteFolder)->in('app/')->count() > 0) {
            $output->writeln('<comment>site dir already exists</comment>');
            exit(0);
        }

        $filesystem = new Filesystem();
        $filesystem->mkdir('app/'.$projectSiteFolder);

        $filesystem->symlink('../core/bitrix', 'app/'.$projectSiteFolder.'/bitrix');
        $filesystem->symlink('../core/upload', 'app/'.$projectSiteFolder.'/upload');
        $filesystem->symlink('../core/local', 'app/'.$projectSiteFolder.'/local');

        if ($input->getOption('first')) {
            $filesystem->dumpFile('app/'.$projectSiteFolder.'/bitrixsetup.php', file_get_contents('http://1c-bitrix.ru/download/scripts/bitrixsetup.php'));
        }

        $output->writeln('<info>Сайт успешно создан. docRoot = '.getcwd().'/app/'.$projectSiteFolder.'/ Пропишите в настройках веб-сервера.</info>');
    }
}