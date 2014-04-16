<?php
/**
 * Created by PhpStorm.
 * User: andkon
 * Date: 10.04.14
 * Time: 15:29
 */

namespace dbmap\fields;

use dbmap\base\DbMap;
use dbmap\base\Field;

trait Name
{
    use Field;

    public $name;

    /**
     * Возвращает модель по полю name
     *
     * @param string $name имя
     *
     * @return DbMap|null
     */
    public static function findByName($name)
    {
        $result = self::_getResult($name);
        if (!isset($result[0])) {
            return null;
        }

        return array_pop($result);
    }

    /**
     * Возвращает результат выполнения запроса преобразованный в модели
     *
     * @param string $name имя
     *
     * @return \dbmap\base\DbMap[]
     */
    private static function _getResult($name)
    {
        /** @var DbMap $class */
        $class  = get_called_class();
        $sql    = 'select * from ' . Field::getTable($class) . ' where name = :name';
        $result = $class::findBySql($sql, [':name' => $name]);

        return $result;
    }

    public static function byNameAll($name)
    {

        return self::_getResult($name);
    }
} 