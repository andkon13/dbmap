<?php
/**
 * Created by PhpStorm.
 * User: andkon
 * Date: 09.07.15
 * Time: 18:55
 */

namespace dbmap\fields;

/**
 * Class Updated
 *
 * @package dbmap\fields
 */
trait Updated
{
    public $updated;

    /**
     * @return bool
     */
    public function updatedValidator(&$val)
    {
        $format = (isset($this->dateFormat)) ? $this->dateFormat : 'Y-m-d H:i:s';
        if (!$this->getIsNew()) {
            $val = date($format);
        }

        return true;
    }
}
