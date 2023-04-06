<?php
/**
 * Created by PhpStorm.
 * User: vyfvfv
 * Date: 02.06.15
 * Time: 21:40
 */

namespace QSoft\Foundation\Component;

use Bitrix\Main\Application;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
use Bitrix\Iblock\InheritedProperty\SectionValues;
use Bitrix\Iblock\InheritedProperty\ElementValues;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use QSoft\Foundation\Exception\HttpException;
use QSoft\Foundation\Exception\PageNotFoundException;
use QSoft\Foundation\Traits\AjaxKeyGenerator;

class Exception extends SystemException {}

abstract class BaseComponent extends \CBitrixComponent implements AjaxKeyGenerated {

    use AjaxKeyGenerator;

    protected $restful = false;

    protected $requireModules = [];

    public function onPrepareComponentParams($arParams)
    {
        if (!is_array($arParams)) return $arParams;

        foreach($arParams as $key => $param) {
            if (is_array($param)) {
                $arParams[$key] = self::onPrepareComponentParams($param);
            } elseif (is_string($param) && 0 === strpos($param, '={')) {
               eval('$param = '.substr($param, 2, strlen($param)-3).';');
                $arParams[$key] = $param;
            }
        }

        return $arParams;
    }



    public function executeComponent()
    {
        try {

            $this->checkModules();
            $this->beforeExecuteComponent();

            if (true === $this->restful) {

                $dispatcher = \FastRoute\cachedDispatcher(function (RouteCollector $r) {
                    $this->setRouting($r);
                }, [
                    'cacheFile' => Loader::getDocumentRoot() . Application::getPersonalRoot() . '/cache/route.cache',
                    'cacheDisabled' => true,     /* optional, enabled by default */
                    'dispatcher' => 'QSoft\\Foundation\\Component\\Router\\Dispatcher'
                ]);

                global $APPLICATION;

                $server = Context::getCurrent()->getServer();
                $routeInfo = $dispatcher->dispatch($server->getRequestMethod(), $APPLICATION->GetCurPage(false));

                switch ($routeInfo[0]) {

                    case Dispatcher::NOT_FOUND:
                        $this->throwPageNotFound();
                        break;
                    case Dispatcher::METHOD_NOT_ALLOWED:
                        throw new HttpException(405);
                        break;
                    case Dispatcher::FOUND:
                        $handler = $routeInfo[1];
                        $vars = $routeInfo[2];
                        if (!method_exists($this, $handler)) {
                            throw new \BadMethodCallException(500);
                        }
                        return call_user_func_array([$this, $handler], $vars);
                        break;
                }

                return;
            }

            return $this->run();

        } catch (SystemException $e) {

            $this->errorHandler($e);

        }
    }

    public function getComponentKey($parent = false)
    {
        global $APPLICATION;

        if (array_key_exists('AJAX_KEY', $this->arParams)) {
            return $this->arParams['AJAX_KEY'];
        }

        if (true === $parent && $this->getParent()) {
            $componentName = $this->getParent()->getName();
            $componentTemplate = $this->getParent()->getTemplateName() ?: '.default';
        } else {
            $componentName = $this->getName();
            $componentTemplate = $this->getTemplateName() ?: '.default';
        }

        $realPath = Context::getCurrent()->getServer()->get('REAL_FILE_PATH') ?: Context::getCurrent()->getServer()->getPhpSelf();

        return $this->generateKey($componentName, $componentTemplate, $realPath);
    }

    protected function errorHandler(SystemException $e) {

        global $USER;

        if ($USER->IsAdmin()) {
            $this->abortResultCache();
            ShowError($e->getMessage());
        }

    }

    protected function renderView($data = [], $view = '') {
        $this->arResult = array_merge($this->arResult, $data);
        $this->includeComponentTemplate($view);
        return 'view';
    }


    public function throwPageNotFound()
    {
        $this->abortResultCache();

        throw new PageNotFoundException;
    }

    protected function checkModules()
    {
        if (!is_array($this->requireModules) || 0 === count($this->requireModules)) {
            return true;
        }

        foreach ($this->requireModules as $module) {

            if (!Loader::includeModule($module)) {
                throw new Exception(sprintf('module %s not found', $module));
            }

        }
    }

    protected function getSeoParams($iblockId, $id, $isSection = false)
    {
        if ($isSection) {
            return (new SectionValues($iblockId, $id))->getValues();
        }

        return (new ElementValues($iblockId, $id))->getValues();
    }

    protected function beforeExecuteComponent() {}

    public function setRouting(RouteCollector $r) {}
    public function run() {
        return null;
    }

}
