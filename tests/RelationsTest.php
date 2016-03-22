<?php
/**
 * Created by PhpStorm.
 * User: andkon
 * Date: 10.07.15
 * Time: 14:25
 */

namespace tests;

use dbmap\base\Pdo;
use dbmap\Client;
use dbmap\User;

/**
 * Class RelationsTest
 */
class RelationsTest extends Base
{
    static private $revertTable = true;

    public function testHasMany()
    {
        /** @var User $user */
        $user    = User::findById(1);
        $clients = $user->clients;
        if (!is_array($clients)) {
            throw new \PHPUnit_Framework_Exception('result must by array');
        } elseif (!$clients[0] instanceof Client) {
            throw new \PHPUnit_Framework_Exception('result must by array of Client');
        }

        error_reporting('~' . E_ERROR);
        $clientId               = $user->clients[0]->id;
        $user->clients[0]->name = 'new name';
        $user->saveRelations    = true;
        $res                    = $user->save();
        if (!$res) {
            throw new \PHPUnit_Framework_Exception('error save');
        }

        /** @var Client $client */
        $client = Client::findById($clientId);
        if ($client->name !== 'new name') {
            throw new \PHPUnit_Framework_Exception('error saveRelations');
        }

        /** @var User $user */
        $user                   = User::findById($user->id);
        $clientId               = $user->clients[1]->id;
        $clientName             = $user->clients[1]->name;
        $user->saveRelations    = false;
        $user->clients[1]->name = 'not save';
        $user->name             = 'must save';
        $res                    = $user->save();
        if (!$res) {
            throw new \PHPUnit_Framework_Exception('save error');
        }

        /** @var Client $client */
        $client = Client::findById($clientId);
        if ($client->name !== $clientName || $user->name !== 'must save') {
            throw new \PHPUnit_Framework_Exception('error saveRelations');
        }
    }

    protected function setUp()
    {
        parent::setUp();
        if (self::$revertTable) {
            $sql = '
                START TRANSACTION ;
                SET FOREIGN_KEY_CHECKS = 0;
                TRUNCATE user;
                TRUNCATE client;
                INSERT INTO user (name, status, created) VALUES ("test", 1, NOW());
                INSERT INTO client (user_id, name, status, created) VALUES (1, "client 1", 1, NOW());
                INSERT INTO client (user_id, name, status, created) VALUES (1, "client 2", 1, NOW());
                INSERT INTO client (user_id, name, status, created) VALUES (1, "client 3", 1, NOW());
                SET FOREIGN_KEY_CHECKS = 1;
                COMMIT ;
            ';
            Pdo::getInstance()->execute($sql);
            self::$revertTable = false;
        }
    }
}
