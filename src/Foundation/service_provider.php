<?php
if (defined('QSOFT_APPLICATION_ROOT')) {
    if (file_exists(QSOFT_APPLICATION_ROOT.'/.env')) {
        Dotenv::load(QSOFT_APPLICATION_ROOT);
    }

    if (file_exists(QSOFT_APPLICATION_ROOT.'/.const')) {
        require_once QSOFT_APPLICATION_ROOT.'/.const';
    }
}

use QSoft\Foundation\Disposer;

if (defined('DISPOSER_APP')) {
    Disposer::add(new \QSoft\Foundation\Console\Commands\CreateProjectFinalize());
    Disposer::add(new \QSoft\Foundation\Console\Commands\CreateSite());
    Disposer::add(new \QSoft\Foundation\Console\Commands\DumpConstant());
    Disposer::add(new \QSoft\Foundation\Console\Commands\UrlRewriteReindex());
    Disposer::add(new \QSoft\Foundation\Console\Commands\ClearCache());
}