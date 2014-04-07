<?php
/**
 * Created by PhpStorm.
 * User: andkon
 * Date: 06.04.14
 * Time: 23:28
 */

function my_autoloader($class)
{
    $path = explode('\\', $class);
    $path = implode(DIRECTORY_SEPARATOR, $path);
    $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . $path . '.php';
    if (file_exists($path)) {
        include_once $path;

        return true;
    }

    return false;
}

spl_autoload_register('my_autoloader');