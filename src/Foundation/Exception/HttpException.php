<?php
/**
 * Created by PhpStorm.
 * User: vyfvfv
 * Date: 03.06.15
 * Time: 14:37
 */

namespace QSoft\Foundation\Exception;


use Exception;

class HttpException extends \Exception {

    public function __construct($code)
    {
        parent::__construct();

        header_remove();
        http_response_code($code);
        die();
    }


}