<?php
/**
 * Created by PhpStorm.
 * User: andkon
 * Date: 07.04.14
 * Time: 0:19
 */

namespace dbmap\fields;

use dbmap\base\DbMap;

/**
 * Class Id
 *
 * @package dbmap\fields
 */
trait Id
{
    /** @var int */
    public $id;

    /**
     * Возвращает модель по id
     *
     * @param $id
     *
     * @return static|null
     */
    public static function findById($id)
    {
        /** @var DbMap $class */
        $sql    = 'select * from ' . static::getTableName() . ' where id = ?';
        $result = static::findBySql($sql, [$id]);
        if (!isset($result[0])) {
            return null;
        }

        return array_pop($result);
    }
} 