<?php
/**
 * Created by PhpStorm.
 * User: andkon
 * Date: 15.04.14
 * Time: 18:26
 */

namespace dbmap\validators;

use dbmap\base\Validator;

trait Email
{
    use Validator

    public function _validate(&$value)
    {
        $this->_error = null;
        $valid_value  = filter_var($value, FILTER_VALIDATE_EMAIL);
        if ($valid_value !== false) {
            $value = $valid_value;

            return true;
        }

        $this->_error = 'is not correct EMAIL';

        return false;
    }
} 