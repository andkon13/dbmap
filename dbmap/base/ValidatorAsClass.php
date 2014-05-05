<?php
/**
 * Created by PhpStorm.
 * User: andkon
 * Date: 04.05.2014
 * Time: 17:28
 */

namespace dbmap\base;


class ValidatorAsClass
{
    private static $_instance = false;

    public static function validate(&$value)
    {
        return self::getInstance()->_validate($value);
    }

    public static function getInstance()
    {
        if (!self::$_instance) {
            $class           = new \ReflectionClass(get_called_class());
            self::$_instance = $class->newInstance();
        }

        return self::$_instance;
    }
} 