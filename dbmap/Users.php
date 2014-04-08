<?php
/**
 * Created by PhpStorm.
 * User: andkon
 * Date: 07.04.14
 * Time: 0:17
 */

namespace dbmap;


use dbmap\base\DbMap;

class Users extends DbMap
{
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