<?php
/**
 * Created by PhpStorm.
 * User: vyfvfv
 * Date: 03.06.15
 * Time: 14:34
 */

namespace QSoft\Foundation\Exception;

class PageNotFoundException extends HttpException {

    public function __construct()
    {
        global $APPLICATION;

        if(strpos($APPLICATION->GetCurUri(), "/bitrix/admin/") === 0)
        {

            $_SERVER["REAL_FILE_PATH"] = "/bitrix/admin/404.php";
            include($_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/404.php");
            die();
        }

        parent::__construct(404);
    }

}