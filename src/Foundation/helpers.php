<?php

use Illuminate\Support\Str;

if ( ! function_exists('array_undot')) {

    function array_undot($array)
    {
        $results = array();

        foreach ($array as $key => $value) {
            array_set($results, $key, $value);
        }

        return $results;
    }
}

if ( ! function_exists('array_diff_recursive')) {

    function array_diff_recursive($array1, $array2, $strict = false) {

        $returnArray = [];

        foreach ($array1 as $key => $value) {

            if (!array_key_exists($key, $array2)) {
                $returnArray[$key] = $value;
                continue;
            }

            if (is_array($value)) {
                $diffResult = array_diff_recursive($array1[$key], $array2[$key]);
                if (!empty($diffResult))
                    $returnArray[$key] = $value;

                continue;
            }

            if ($strict ? $array1[$key] !== $array2[$key] : $array1[$key] != $array2[$key]) {
                $returnArray[$key] = $value;
                continue;
            }
        }

        return $returnArray;
    }

}

if ( ! function_exists('array_aliases')) {

    function array_aliases($array, $aliases)
    {
        $results = array();

        foreach ($array as $key => $value) {
            $results[$aliases[$key] ?: $key] = $value;
        }

        return $results;
    }
}

if ( ! function_exists('env'))
{
    /**
     * Gets the value of an environment variable. Supports boolean, empty and null.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function env($key, $default = null)
    {
        $value = getenv($key);
        if ($value === false) return value($default);
        switch (strtolower($value))
        {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return;
        }
        if (Str::startsWith($value, '"') && Str::endsWith($value, '"'))
        {
            return substr($value, 1, -1);
        }
        return $value;
    }
}

if ( ! function_exists('url_param')) {

    function url_param($url, $add = null, $remove = null)
    {
        if (null === $remove && null === $add) {
            return $url;
        }

        $url_parts = parse_url($url);
        parse_str($url_parts['query'], $params);

        if (null !== $remove) {
            foreach ($remove as $param) {
                unset($params[$param]);
            }
        }

        if (null !== $add) {
            foreach ($add as $param => $value) {
                $params[$param] = $value;
            }
        }

        $url_parts['query'] = http_build_query($params);
        return http_build_url($url_parts);
    }
}

if ( ! function_exists('http_build_url')) {

    function http_build_url($url, $parts = null) {

        if (func_num_args() === 1) {
            $parts = $url;
        }
        else
        {
            $parts = array_merge(parse_url($url), $parts);
        }

        $return = '';

        if (!empty($parts['scheme'])) {
            $return .= $parts['scheme'] . '://';
        }

        $return .= ($parts['host'] ?: '') . ($parts['port'] ? ':'.$parts['port'] : '') . $parts['path'] . ($parts['query'] ? '?' . $parts['query'] : '');

        return $return;
    }

}

if (!function_exists('array_swap')) {

    /**
     * Функция меняет значения элементов массива $key и $key2 местами
     * @param array $array исходный массив
     * @param $key ключ элемента массива
     * @param $key2 ключ элемента массива
     * @return bool true замена произошла, false замена не произошла
     */
    function array_swap(array &$array, $key, $key2)
    {
        if (isset($array[$key]) && isset($array[$key2])) {
            list($array[$key], $array[$key2]) = array($array[$key2], $array[$key]);
            return true;
        }

        return false;
    }

}
