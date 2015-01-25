<?php
/**
 * Created by PhpStorm.
 * User: andkon
 * Date: 07.04.14
 * Time: 0:19
 */

namespace dbmap\fields;

use dbmap\base\DbMap;
use dbmap\base\Field;
use dbmap\validators\Int;

/**
 * Class Id
 *
 * @package dbmap\fields
 */
trait Id
{
    use Field;

    /** @var  int */
    public $id;

    /**
     * @param $id
     *
     * @return mixed|null
     */
    public static function findById($id)
    {
        /** @var DbMap $class */
        $class  = get_called_class();
        $sql = 'select * from ' . self::getTableName() . ' where id = ?';
        $result = $class::findBySql($sql, [$id]);
        if (!isset($result[0])) {
            return null;
        }

        return array_pop($result);
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function idValidator($id)
    {
        if (Int::validate($id)) {
            $this->errors[] = Int::getLastError();
        }

        return true;
    }
} 