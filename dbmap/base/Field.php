<?php
/**
 * Created by PhpStorm.
 * User: andkon
 * Date: 07.04.14
 * Time: 0:51
 */

namespace dbmap\base;


trait Field
{
    /**
     * @param string|DbMap $class
     *
     * @return mixed
     */
    public static function getTable($class)
    {
        $table = $class::getTableName();

        return $table;
    }
} 