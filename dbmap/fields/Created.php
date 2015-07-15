<?php
/**
 * Created by PhpStorm.
 * User: andkon
 * Date: 09.07.15
 * Time: 19:00
 */

namespace dbmap\fields;

/**
 * Class Created
 *
 * @package dbmap\fields
 */
trait Created
{
    public $created;

    /**
     * @return bool
     */
    public function createdValidator(&$val)
    {
        $format = (isset($this->dateFormat)) ? $this->dateFormat : 'Y-m-d H:i:s';
        if (empty($this->created)) {
            $val = date($format);
        }

        return true;
    }
}
