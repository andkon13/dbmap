<?php
/**
 * Created by PhpStorm.
 * User: andkon
 * Date: 15.04.14
 * Time: 17:17
 */

namespace dbmap;

use dbmap\base\DbMap;
use dbmap\fields\Created;
use dbmap\fields\Name;
use dbmap\fields\Status;
use dbmap\fields\Updated;

/**
 * Class Client
 *
 * @package dbmap
 *
 * @property User $user
 */
class Client extends DbMap
{
    use Name, Updated, Created, Status;

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
     * Возвращает связи
     *
     * @return array
     */
    public static function relations()
    {
        return [
            'user' => [self::BELONG_TO, User::class, 'user_id']
        ];
    }

}