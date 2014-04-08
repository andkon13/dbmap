<?php
/**
 * Created by PhpStorm.
 * User: andkon
 * Date: 07.04.14
 * Time: 0:01
 */

namespace dbmap\base;


use dbmap\fields\Id;

abstract class DbMap
{
    use Id;
    /** @var bool|Pdo */
    static private $_db = false;

    private $_isNew = true;

    function __construct($isNew)
    {
        $this->_isNew = $isNew;
    }

    /**
     * @return bool|\PDO
     */
    public static function getDb()
    {
        if (!self::$_db) {
            self::$_db = Pdo::getInstance();
        }

        return self::$_db;
    }

    public function validate()
    {

    }

    public function setAttributes($attributes)
    {
        $vars = get_object_vars($this);
        foreach ($attributes as $field => $val) {
            if (array_key_exists($field, $vars)) {
                $this->$field = $val;
            }
        }

        return $this;
    }

    /**
     * return table name
     *
     * @return string
     */
    abstract static public function getTableName();
}
