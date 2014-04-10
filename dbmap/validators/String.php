<?php
/**
 * Created by PhpStorm.
 * User: andkon
 * Date: 07.04.14
 * Time: 22:18
 */

namespace dbmap\validators;


use dbmap\base\Validator;

trait String
{
    use Validator;

    public function _validate($value, $length = 255)
    {
        if (strlen($value) > $length) {
            $this->_error = 'string too long (limit ' . $length . ')';

            return false;
        }

        return true;
    }
} 