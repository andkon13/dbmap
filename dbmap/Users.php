<?php
/**
 * Created by PhpStorm.
 * User: andkon
 * Date: 07.04.14
 * Time: 0:17
 */

namespace dbmap;


use dbmap\base\DbMap;
use dbmap\fields\Id;

class Users extends DbMap
{
    use Id;

    /**
     * return table name
     *
     * @return string
     */
    static public function getTableName()
    {
        return 'users';
    }
}