<?php
/**
 * Created by PhpStorm.
 * User: andkon
 * Date: 10.07.15
 * Time: 11:49
 */

namespace dbmap\fields;

/**
 * Class Status
 *
 * @package dbmap\fields
 */
trait Status
{
    public $status;

    /**
     * @param $status
     *
     * @return bool
     */
    public function statusValidator(&$status)
    {
        $status = intval($status);

        return true;
    }
}
