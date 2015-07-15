<?php
/**
 * Created by PhpStorm.
 * User: andkon
 * Date: 07.04.14
 * Time: 0:17
 */

namespace dbmap;

use dbmap\base\DbMap;
use dbmap\fields\Created;
use dbmap\fields\Name;
use dbmap\fields\Status;
use dbmap\fields\Updated;

/**
 * Class User
 *
 * @package dbmap
 * @property Client[] $clients
 */
class User extends DbMap
{
    use Name, Updated, Created, Status;

    /**
     * return table name
     *
     * @return string
     */
    static public function getTableName()
    {
        return 'user';
    }

    /**
     * Возвращает связи
     *
     * @return array
     */
    public static function relations()
    {
        return [
            'clients' => [self::HAS_MANY, Client::class, 'user_id']
        ];
    }

    /**
     * @param mixed $name
     *
     * @return bool
     */
    public function nameValidator($name)
    {
        if (empty($name)) {
            $this->addError('name', 'is not null');

            return false;
        }

        return true;
    }
}
