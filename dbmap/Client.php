<?php
/**
 * Created by PhpStorm.
 * User: andkon
 * Date: 15.04.14
 * Time: 17:17
 */

namespace dbmap;

use dbmap\base\DbMap;
use dbmap\base\Field;
use dbmap\fields\Name;
use dbmap\validators\Int;
use dbmap\validators\IntClass;

class Client extends DbMap
{
    use Name;

    public $user_id;

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

    public function user_idValidator(&$uset_id)
    {
        if (!IntClass::validate($uset_id)) {
            $this->_error = 'user_id ' . IntClass::getInstance()->getLastError();

            return false;
        }

        return true;
    }
}