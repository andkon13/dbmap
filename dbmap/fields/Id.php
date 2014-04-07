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

    public static function byId($id)
    {
        $class = get_called_class();
        $sql   = 'select * from ' . Field::getTable($class) . ' where id = ?';
        $attrib   = $class::getDb()->getRow($sql, [$id]);
        if (!is_array($attrib)) {
            return null;
        }

        /** @var DbMap $class */
        $class = new $class(false);

        $class->setAttributes($attrib);

        return $class;
    }
} 