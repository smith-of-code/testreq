<?php
/**
 * Created by PhpStorm.
 * User: vyfvfv
 * Date: 03.06.15
 * Time: 13:09
 */

namespace QSoft\Foundation\Component;

use Bitrix\Main\Component\ParametersTable;
use Bitrix\Main\Context;
use Bitrix\Main\Page\Frame;
use QSoft\Foundation\Exception\PageNotFoundException;
use QSoft\Foundation\Traits\AjaxKeyGenerator;

/**
 * Class AjaxLoader
 * Проверяет AJAX запрос на валидность. В случае успеха,
 * на страницу подключается требуемый компонент
 *
 * @package QSoft\Foundation\Component
 */
class AjaxLoader implements AjaxKeyGenerated {

    use AjaxKeyGenerator;

    /**
     * Хранит результат выполнения метода isValidAjaxRequest
     * @see AjaxLoader::isValidAjaxRequest()
     * @var bool
     */
    private $isAjaxRequest;

    /**
     * Хранит результат выполнения метода parseKey
     * @see AjaxLoader::parseKey()
     * @var
     */
    private $keyData;

    /**
     * Хранит результат выполнения метода getKey
     * @see AjaxLoader::getKey()
     * @var
     */
    private $key;

    /**
     * Метод проверяет запрос на валидность.
     * 1. Проверяется через битриксовый метод Frame::getInstance()->isAjaxRequest()
     * 2. Если в запросе есть ключ ssi или передан заголовок X-Requested-With = XMLHttpRequest
     * 3. Проверка ключа на валидность, должен содежржать 3 блока {block1}|{block2}|{block3}
     *
     * @return bool
     */
    public function isValidAjaxRequest()
    {
        if (null === $this->isAjaxRequest) {

            $this->isAjaxRequest = Frame::getInstance()->isAjaxRequest();
            $this->isAjaxRequest = array_key_exists('ssi', $_GET) || $this->isAjaxRequest || 'xmlhttprequest' === strtolower(Context::getCurrent()->getServer()->get('HTTP_X_REQUESTED_WITH'));
            $this->isAjaxRequest = $this->isAjaxRequest && $this->isKeyValid();
        }

        return $this->isAjaxRequest;

    }

    /**
     * Подключает требуемы компонент.
     *
     * @throws PageNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     */
    public function runComponent()
    {
        if (false === $this->isValidAjaxRequest()) {
            throw new PageNotFoundException;
        }

        list($componentName, $componentTemplate, $componentPath) = $this->parseKey($this->getKey());
        $componentInfo = ParametersTable::getList([
            'filter' => [
                'SITE_ID' => SITE_ID,
                'COMPONENT_NAME' => $componentName,
                'TEMPLATE_NAME' => $componentTemplate,
                'REAL_PATH' => $componentPath
            ]
        ])->fetch();

        if (false === $componentInfo) {
            throw new PageNotFoundException;
        }

        global $APPLICATION;

        ob_start();
        $result = $APPLICATION->IncludeComponent(
            $componentInfo['COMPONENT_NAME'],
            $componentInfo['TEMPLATE_NAME'],
            unserialize($componentInfo['PARAMETERS'])
        );
        $data = ob_get_contents();
        ob_end_clean();

        if (null === $result) {
            echo $data;
        } elseif(is_array($result)) {
            header('Content-type: application/json');
            echo json_encode($result);
        } else {
            echo $result;
        }

        die();
    }

    /**
     * Проверка валидность ключа key в GET запросе
     *
     * @return bool
     */
    private function isKeyValid()
    {
        return 3 <= count($this->parseKey($this->getKey()));
    }

    /**
     * Разбор ключа
     *
     * @return array
     */
    private function parseKey($key)
    {
        if (null === $this->keyData) {
            $this->keyData = $this->decodeKey($key);
        }
        return $this->keyData;
    }

    /**
     * Получение ключа из запроса
     *
     * @return mixed
     */
    private function getKey()
    {
        if (null === $this->key) {
            $this->key = filter_input(INPUT_GET, 'key', FILTER_SANITIZE_STRING);
        }

        return $this->key;
    }
}