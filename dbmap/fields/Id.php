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
    use Field, Int;

    /** @var  int */
    public $id;

    public static function findById($id)
    {
        /** @var DbMap $class */
        $class  = get_called_class();
        $sql    = 'select * from ' . Field::getTable($class) . ' where id = ?';
        $result = $class::getBySql($sql, [$id]);
        if (!isset($result[0])) {
            return null;
        }

        return array_pop($result);
    }

    public function idValidator($id)
    {
        if (!$this->_validate($id)) {
            $this->errors[] = $this->getLastError();
        }

        return true;
    }
} 