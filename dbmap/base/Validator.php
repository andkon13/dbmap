<?php
/**
 * Created by PhpStorm.
 * User: andkon
 * Date: 07.04.14
 * Time: 22:04
 */

namespace dbmap\base;
/**
 * Class Validator
 *
 * @package dbmap\base
 */
abstract class Validator
{
    protected static $error = null;

    /**
     * @return null|string
     */
    public static function getLastError()
    {
        return self::$error;
    }

    /**
     * Проверяет значение
     *
     * @param mixed      $value
     * @param null|mixed $options
     *
     * @return bool
     */
    abstract static public function validate(&$value, $options = null);
}
