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

    public function validate($value)
    {
        $this->_error = null;
        $value        = intval($value);
        if (is_int($value)) {
            return true;
        }

        $this->_error = 'is not integer';

        return false;
    }
} 