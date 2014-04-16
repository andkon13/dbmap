<?php
/**
 * Created by PhpStorm.
 * User: andkon
 * Date: 07.04.14
 * Time: 21:57
 */

namespace dbmap\validators;

use dbmap\base\Validator;

trait Int
{
    use Validator;

    public function _validate(&$value)
    {
        $this->_error = null;
        $valid_value = filter_var($value, FILTER_VALIDATE_INT);
        if ($valid_value !== false) {
            $value = $valid_value;

            return true;
        }

        $this->_error = 'is not integer';

        return false;
    }
}
