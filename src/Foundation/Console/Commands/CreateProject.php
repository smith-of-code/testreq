<?php

namespace QSoft\Foundation\Console\Commands;

use Composer\Script\Event;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;

class CreateProject
{
    public static function execute(Event $event)
    {
        $finder = new Finder();
        if ($finder->in('./')->files()->name('create-project.lock')->count() > 0) {
            $event->getIO()->write('<comment>Project already created</comment>');
            exit(0);
        }

        $projectCode = null;

        while (!$projectCode) {
            $projectCode = $event->getIO()->ask('What is you project code name (Ex. yaposha) ? ');
        }

        $filesystem = new Filesystem();
        $filesystem->dumpFile('composer.json', str_replace('#PROJECT_NAME#', ucfirst($projectCode), file_get_contents('composer.json')));
        $filesystem->mkdir('app/core/local/src/'.ucfirst($projectCode));
        $filesystem->mkdir('app/core/local/src/'.ucfirst($projectCode).'/Migrate');
        $filesystem->mkdir('app/core/local/src/'.ucfirst($projectCode).'/Tests');
        $filesystem->touch('app/core/local/src/'.ucfirst($projectCode).'/Migrate/.gitkeep');
        $filesystem->touch('app/core/local/src/'.ucfirst($projectCode).'/Tests/.gitkeep');
        $filesystem->touch('app/core/local/src/'.ucfirst($projectCode).'/.gitkeep');

        file_put_contents('composer.json', str_replace('#PROJECT_NAME#', ucfirst($projectCode), file_get_contents('composer.json')));

        exec('composer dump-autoload');

        exec('php disposer bitrix:create-site --first 1>&2');

        $filesystem->touch('create-project.lock');

        $event->getIO()->write('Project created');
    }
}