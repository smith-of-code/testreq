<?php
/**
 * Created by PhpStorm.
 * User: vyfvfv
 * Date: 03.06.15
 * Time: 15:42
 */

namespace QSoft\Foundation\Component;


interface AjaxKeyGenerated {

    public function generateKey($componentName, $componentTemplate, $realPath, $additionalParams = []);
    public function decodeKey($key);

}