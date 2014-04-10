<?php
/**
 * Created by PhpStorm.
 * User: andkon
 * Date: 07.04.14
 * Time: 22:04
 */

namespace dbmap\base;

trait Validator
{
    protected $_error = null;

    /**
     * @return null|string
     */
    public function getLastError()
    {
        return $this->_error;
    }

    public function _validate($value)
    {
        return true;
    }
} 