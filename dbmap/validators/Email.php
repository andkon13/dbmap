<?php
/**
 * Created by PhpStorm.
 * User: andkon
 * Date: 15.04.14
 * Time: 18:26
 */

namespace dbmap\validators;

use dbmap\base\Validator;

/**
 * Class Email
 *
 * @package dbmap\validators
 */
class Email extends Validator
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
        $valid_value = filter_var($value, FILTER_VALIDATE_EMAIL);
        if ($valid_value !== false) {
            $value = $valid_value;

            return true;
        }

        self::$error = 'is not correct EMAIL';

        return false;
    }
} 