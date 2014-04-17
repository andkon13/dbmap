<?php
/**
 * Created by PhpStorm.
 * User: andkon
 * Date: 15.04.14
 * Time: 17:17
 */

namespace dbmap;

use dbmap\base\DbMap;
use dbmap\fields\Name;

class Client extends DbMap
{
    use Name;

    /**
     * return table name
     *
     * @return string
     */
    static public function getTableName()
    {
        return 'client';
    }

    /**
     * Возвращает связанные модели
     *
     * @return array
     */
    public static function relations()
    {
        return [
            'Users' => [self::HAS_MANY, 'Users', 'client_id'],
        ];
    }
}