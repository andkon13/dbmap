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

/**
 * Class Name
 *
 * @package dbmap\fields
 */
trait Name
{
    use Field;

    /**
     * @var
     */
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
        $result = self::getResult($name, 1);
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
    private static function getResult($name, $limit = null)
    {
        /** @var DbMap $class */
        $class = get_called_class();
        $sql   = 'select * from ' . self::getTableName() . ' where name = :name';
        $sql .= ($limit) ? ' limit 0, ' . $limit : '';
        $result = $class::findBySql($sql, [':name' => $name]);

        return $result;
    }

    /**
     * @param $name
     *
     * @return \dbmap\base\DbMap[]
     */
    public static function findByNameAll($name)
    {

        return self::getResult($name);
    }
}
