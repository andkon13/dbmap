<?php
/**
 * Created by PhpStorm.
 * User: andkon
 * Date: 07.04.14
 * Time: 21:57
 */

namespace dbmap\validators;

use dbmap\base\Validator;

/**
 * Class Int
 *
 * @package dbmap\validators
 */
class Int extends Validator
{
    /**
     * @inheritdoc
     *
     * @param mixed $value
     * @param null  $options
     *
     * @return bool
     */
    public static function validate(&$value, $options = null)
    {
        self::$error = null;
        $valid_value = filter_var($value, FILTER_VALIDATE_INT);
        if ($valid_value !== false) {
            $value = $valid_value;

            return true;
        }

        self::$error = 'is not integer';

        return false;
    }
}
