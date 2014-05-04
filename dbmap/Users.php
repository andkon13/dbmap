<?php
/**
 * Created by PhpStorm.
 * User: andkon
 * Date: 07.04.14
 * Time: 0:17
 */

namespace dbmap;

use dbmap\base\DbMap;
use dbmap\fields\Name;

/**
 * Class Users
 *
 * @package dbmap
 * @property Client[] $Client
 */
class Users extends DbMap
{
    /**
     * return table name
     *
     * @return string
     */
    static public function getTableName()
    {
        return 'users';
    }

    public static function relations()
    {
        return [
            //'Client' => [self::MANY_MANY, 'Client', 'user_client', 'user_id', 'client_id'],
            'Client' => [self::HAS_ONE, 'Client', 'user_id'],
        ];
    }
}
