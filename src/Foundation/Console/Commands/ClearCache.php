<?php
/**
 * Created by PhpStorm.
 * User: vyfvfv
 * Date: 29.05.15
 * Time: 13:50
 */

namespace QSoft\Foundation\Console\Commands;

use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Loader;
use CPHPCacheMemcacheCluster;
use Memcache;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ClearCache extends Command {

    protected function configure()
    {
        parent::configure();

        $this
            ->setName('deploy:clear-cache')
            ->setDescription('Выполняет очистку кеша (memcached)')
            ->addOption(
                'delay',
                0,
                InputOption::VALUE_REQUIRED,
                'Величина задержки в секундах перед аннулированием записей.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = Configuration::getValue('cache')['memcache'];
        $cache = new \Memcache;
        $cache->addserver($config['host'], $config['port']);
        $cache->flush();
    }
}