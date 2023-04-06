<?php
/**
 * Created by PhpStorm.
 * User: vyfvfv
 * Date: 03.06.15
 * Time: 15:27
 */

namespace QSoft\Foundation\Traits;

trait AjaxKeyGenerator {

    public function generateKey($componentName, $componentTemplate, $realPath, $additionalParams = []){

        return base64_encode(implode('|', [$componentName, $componentTemplate, $realPath, serialize($additionalParams)]));

    }

    public function decodeKey($key) {

        return explode('|', base64_decode($key));

    }

}