<?php
/**
 * Created by PhpStorm.
 * User: andkon
 * Date: 06.04.14
 * Time: 22:59
 */

namespace dbmap\base;


class Pdo extends \PDO
{
    private static $_instance = false;
    /** @var string string */
    private static $_configFile = 'config.php';

    /**
     * @var \PDO
     */
    private $_PDO;

    final public function __construct($pdo)
    {
        $this->_PDO = $pdo;
    }

    /**
     * @return bool|Pdo
     * @throws \Exception
     */
    public static function getInstance()
    {
        if (!self::$_instance) {
            self::$_configFile = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . self::$_configFile;
            if (!file_exists(self::$_configFile)) {
                throw new \Exception('DB config not found.');
            }

            $config = require_once self::$_configFile;
            $pdo    = new \PDO(
                "mysql:host=" . $config['host'] . ";dbname=" . $config['dbname'],
                $config['user'],
                $config['pass']
            );

            if (isset($config['character'])) {
                $query = $pdo->prepare("SET NAMES '" . $config['character'] . "'");
                $query->execute();
                $query = $pdo->prepare("SET CHARACTER SET " . $config['character']);
                $query->execute();
            }

            $instance        = new self($pdo);
            self::$_instance = $instance;
        }

        return self::$_instance;
    }

    /**
     * @param string $query
     * @param array  $param
     *
     * @return \PDOStatement
     */
    public static function getResult($query, $param = array())
    {
        $pdo = self::getInstance()->_PDO->prepare($query);
        $pdo->execute($param);

        return $pdo;
    }

    /**
     * @param string $query
     * @param array  $param
     *
     * @return array
     */
    public static function getRow($query, $param = array())
    {
        $pdo = self::getResult($query, $param);

        return $pdo->fetch(\PDO::FETCH_ASSOC);
    }
}
