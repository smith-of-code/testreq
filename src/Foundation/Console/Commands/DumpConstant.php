<?php
/**
 * Created by PhpStorm.
 * User: vyfvfv
 * Date: 29.05.15
 * Time: 13:50
 */

namespace QSoft\Foundation\Console\Commands;

use Bitrix\Main\Loader;
use QSoft\Foundation\Console\Commands\DumpConstant\ConstBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TwigGenerator\Builder\Generator;
use Bitrix\Highloadblock as HL;
use Bitrix\Catalog\GroupTable;

class DumpConstant extends Command {

    protected function configure()
    {
        parent::configure();

        $this
            ->setName('deploy:dump-constant')
            ->setDescription('Выполняет сборку файла с константами')
            ->addOption(
                'with',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'Для каких типов объектов создаются константы? Доступны: iblock, highload, form',
                ['iblock', 'highload', 'form', 'price']
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $types = ['iblock', 'highload', 'form', 'price'];

        if ($input->getOption('with')) {
            $types = $input->getOption('with');
        }
        $builder = new ConstBuilder();
        $builder->setOutputName('.const');

        foreach ($types as $type) {
            switch ($type) {
                case 'iblock':
                    $builder->setVariable('iblock_consts', $this->generateIblockConstants());
                    break;
                case 'highload':
                    $builder->setVariable('highload_consts', $this->generatHehighloadConstants());
                    break;
                case 'form':
                    $builder->setVariable('form_consts', $this->generateFormConstants());
                    break;
                case 'price':
                    $builder->setVariable('price_consts', $this->generatePriceConstants());
                    break;
            }
        }

        $generator = new Generator();
        $generator->setTemplateDirs(array(
            __DIR__.'/DumpConstant/templates'
        ));

        $generator->setMustOverwriteIfExists(true);

        $generator->addBuilder($builder);

        $generator->writeOnDisk(QSOFT_APPLICATION_ROOT);
    }

    private function generateIblockConstants()
    {
        $items = [];

        (new Loader())->includeModule('iblock');

        $iblockList = \CIBlock::GetList([], ['ACTIVE' => 'Y', 'CHECK_PERMISSIONS' => 'N'], false);

        while ($iblock = $iblockList->Fetch()) {

            $items[strtoupper($iblock['IBLOCK_TYPE_ID'])][] = [
                'name' => $iblock['NAME'],
                'site_id' => $iblock['LID'],
                'code' => 'IBLOCK_' . strtoupper($iblock['XML_ID']),
                'id' => $iblock['ID']
            ];
        }

        return $items;
    }

    private function generatHehighloadConstants() 
    {
        $items = [];

        (new Loader())->includeModule('highloadblock');

        $rsData = HL\HighloadBlockTable::getList(array(
            "select" => array('ID', 'NAME', 'TABLE_NAME',
        )));

        while ($ar = $rsData->fetch()){
            $items[] = [
                'name' => $ar['NAME'],
                'code' => 'HIGHLOAD_BLOCK_' . strtoupper($ar['NAME']),
                'id' => $ar['ID']
            ];
        }
        return $items;
    }

    private function generateFormConstants()
    {
        $items = [];

        (new Loader())->includeModule('form');

        $formList = \CForm::GetList(($by = []), ($order = []), ['ACTIVE' => 'Y'], ( $isFiltered = ''));

        while ($form = $formList->Fetch()) {

            $items[] = [
                'name' => $form['NAME'],
                'code' => 'FORM_' . strtoupper($form['SID']),
                'id' => $form['ID']
            ];
        }

        return $items;
    }

    private function generatePriceConstants()
    {
        $items = [];

        (new Loader())->includeModule('catalog');

        $priceList = GroupTable::getList(['select' => ['ID', 'XML_ID', 'NAME']]);

        while ($price = $priceList->fetch()) {
            $items[] = [
                'name' => $price['NAME'],
                'code' => 'PRICE_' . str_replace(['_ACTUAL_PRICE', '_PRICE'], [''], strtoupper($price['XML_ID'])),
                'id' => $price['ID']
            ];
        }

        return $items;
    }
}