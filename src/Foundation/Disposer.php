<?php namespace QSoft\Foundation;

use Illuminate\Console\Command;

class Disposer {

    private static $disposer;

    protected static function getDisposer()
    {
        if ( ! is_null(self::$disposer)) return self::$disposer;

        self::$disposer = new Console\Application();

        return self::$disposer;
    }

    /**
     * Dynamically pass all missing methods to console Artisan.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        return call_user_func_array(array(self::getDisposer(), $method), $parameters);
    }

    public static function addLaravel(Command $command)
    {
        return self::add(with($command, function($work) {
            $work->setLaravel(\Illuminate\Container\Container::getInstance());
            return $work;
        }));
    }
}