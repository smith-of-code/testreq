<?php
/**
 * Created by PhpStorm.
 * User: vyfvfv
 * Date: 29.05.15
 * Time: 13:50
 */

namespace QSoft\Foundation\Console\Commands;

use Bitrix\Main\Application;
use Bitrix\Main\Component\ParametersTable;
use Bitrix\Main\IO\File;
use Bitrix\Main\IO\Path;
use Bitrix\Main\Loader;
use Bitrix\Main\SiteTable;
use Bitrix\Main\UrlRewriter;
use Bitrix\Main\UrlRewriterRuleMaker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UrlRewriteReindex extends Command {

    private $fileOnly = false;

    protected function configure()
    {
        parent::configure();

        $this
            ->setName('deploy:urlrewrite-reindex')
            ->setDescription('Выполняет переиндексацию компонентов.')
            ->addOption('file-only', null, InputOption::VALUE_NONE, 'Выполняет обновление только файла urlrewrite.php')
            ->addOption(
                'site_id',
                null,
                InputOption::VALUE_REQUIRED,
                'Выполняет переиндексацию urlrewrite'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->fileOnly = $input->getOption('file-only');

        $ns = [];

        if ($input->getOption('site_id')) {
            $ns['SITE_ID'] = $input->getOption('site_id');
        }

        $this->removeUrlRewriteFile();
        $this->reindex();

        $output->writeln('Успешно Проиндексированно');
    }

    private function removeUrlRewriteFile()
    {

        $removed = [];

        foreach ($this->getSites() as $site) {

            if (!$this->fileOnly) {
                ParametersTable::deleteBySiteId($site["LID"]);
            }

            if (!in_array($site["LID"], $removed))
            {
                UrlRewriter::delete(
                    $site["LID"],
                    ["!ID" => ""]
                );
                $removed[] = $site["LID"];
            }

        }

    }

    private $sites;

    private function getSites()
    {
        return $this->sites ?: $this->loadSites();
    }

    private function loadSites()
    {
        $this->sites = [];

        $sites = SiteTable::getList(
            [
                "select" => ["LID", "DOC_ROOT", "DIR"],
                "filter" => ["ACTIVE" => "Y"],
            ]
        );

        while ($site = $sites->fetch())
        {
            $this->sites[$site['LID']] = $site;
        }

        return $this->sites;
    }

    private function reindex()
    {
        $baseFile = QSOFT_APPLICATION_ROOT .'/.routes.php';
        $files = [];

        if (file_exists($baseFile)) {
            $files = include $baseFile;
        }

        foreach ($files as $siteId => $fileList) {

            $path = Application::getDocumentRoot().$this->getSites()[$siteId]['DOC_ROOT'];

            foreach ($fileList as $file) {

                $file = '/'.ltrim($file, '/');
                $this->reindexFile($siteId, $path, $file);

            }
        }
    }

    private function reindexFile($siteId, $path, $file)
    {

        $pathAbs = Path::combine($path, $file);

        $_file = new File($pathAbs);
        $fileSrc = $_file->getContents();

        if (!$fileSrc || $fileSrc == "")
            return 0;

        $arComponents = \PHPParser::parseScript($fileSrc);
        for ($i = 0, $cnt = count($arComponents); $i < $cnt; $i++)
        {
            $sef = (is_array($arComponents[$i]["DATA"]["PARAMS"]) && $arComponents[$i]["DATA"]["PARAMS"]["SEF_MODE"] == "Y");

            if (!$this->fileOnly) {

                ParametersTable::add(
                    array(
                        'SITE_ID' => $siteId,
                        'COMPONENT_NAME' => $arComponents[$i]["DATA"]["COMPONENT_NAME"],
                        'TEMPLATE_NAME' => $arComponents[$i]["DATA"]["TEMPLATE_NAME"],
                        'REAL_PATH' => $file,
                        'SEF_MODE' => ($sef? ParametersTable::SEF_MODE : ParametersTable::NOT_SEF_MODE),
                        'SEF_FOLDER' => ($sef? $arComponents[$i]["DATA"]["PARAMS"]["SEF_FOLDER"] : null),
                        'START_CHAR' => $arComponents[$i]["START"],
                        'END_CHAR' => $arComponents[$i]["END"],
                        'PARAMETERS' => serialize($arComponents[$i]["DATA"]["PARAMS"]),
                    )
                );

            }

            if ($sef)
            {
                if (array_key_exists("SEF_RULE", $arComponents[$i]["DATA"]["PARAMS"]))
                {
                    $ruleMaker = new UrlRewriterRuleMaker;
                    $ruleMaker->process($arComponents[$i]["DATA"]["PARAMS"]["SEF_RULE"]);
                    $arFields = array(
                        "CONDITION" => $ruleMaker->getCondition(),
                        "RULE" => $ruleMaker->getRule(),
                        "ID" => $arComponents[$i]["DATA"]["COMPONENT_NAME"],
                        "PATH" => $file,
                        "SORT" => UrlRewriter::DEFAULT_SORT,
                    );
                }
                else
                {
                    $arFields = array(
                        "CONDITION" => "#^".$arComponents[$i]["DATA"]["PARAMS"]["SEF_FOLDER"]."#",
                        "RULE" => "",
                        "ID" => $arComponents[$i]["DATA"]["COMPONENT_NAME"],
                        "PATH" => $file,
                        "SORT" => UrlRewriter::DEFAULT_SORT,
                    );
                }

                UrlRewriter::add($siteId, $arFields);
            }
        }
    }
}