<?php
/**
 * Created by PhpStorm.
 * User: andkon
 * Date: 07.04.14
 * Time: 22:18
 */

namespace dbmap\validators;

use dbmap\base\Validator;

/**
 * Class String
 *
 * @package dbmap\validators
 */
class String extends Validator
{
    /**
     * @inheritdoc
     *
     * @param mixed $value
     * @param int   $length
     *
     * @return bool
     */
    public static function validate(&$value, $length = 255)
    {
        self::$error = null;
        if (strlen($value) > $length) {
            self::$error = 'string too long (limit ' . $length . ')';

            return false;
        }

        return true;
    }
}
