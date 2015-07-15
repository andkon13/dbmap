<?php
/**
 * Created by PhpStorm.
 * User: andkon
 * Date: 10.04.14
 * Time: 15:29
 */

namespace dbmap\fields;

/**
 * Class Name
 *
 * @package dbmap\fields
 */
trait Name
{

    public $name;

    /**
     * Возвращает модель по полю name
     *
     * @param string $name имя
     *
     * @return static|null
     */
    public static function findByName($name)
    {
        $result = self::getResult($name);
        if (!isset($result[0])) {
            return null;
        }

        return array_pop($result);
    }

    /**
     * Возвращает модель по имени
     *
     * @param string $name имя
     *
     * @return static[]
     */
    private static function getResult($name)
    {
        $sql    = 'select * from ' . static::getTableName() . ' where name = :name';
        $result = static::findBySql($sql, [':name' => $name]);

        return $result;
    }

    /**
     * Возвращиет массив по имени
     *
     * @param string $name
     *
     * @return static[]
     */
    public static function byNameAll($name)
    {
        return self::getResult($name);
    }
} 