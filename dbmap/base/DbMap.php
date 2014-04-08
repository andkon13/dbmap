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

    /**
     * Будут ли произведена попытка сохранить данные при выгрузке объекта (__destruct)
     *
     * @var bool
     */
    public $autoSaveChange = false;

    /** @var bool|Pdo */
    static private $_db = false;
    /** @var bool */
    private $_isNew = true;
    /** @var array */
    private $_initAttrubutes = [];

    /**
     * @param bool  $isNew
     * @param array $attributes
     */
    function __construct($isNew, $attributes)
    {
        $this->_isNew = $isNew;
        if (!empty($attributes)) {
            $this->_initAttrubutes = $attributes;
            $this->setAttributes($attributes);
        }
    }

    /**
     * @throws \Exception
     *
     * @return bool|Pdo
     */
    public static function getDb()
    {
        if (!self::$_db) {
            self::$_db = Pdo::getInstance();
        }

        return self::$_db;
    }

    /**
     * @return bool
     */
    public function beforeValidate()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function validate()
    {
        if (!$this->beforeValidate()) {
            return false;
        }

        $attributes = $this->getAttributes();
        $result     = true;
        foreach ($attributes as $field => $value) {
            $validator_func = $field . 'Validator';
            if (method_exists($this, $validator_func)) {
                $result = ($result && $this->$validator_func($value));
            }
        }

        return $result;
    }

    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        if (!$this->beforeSave()) {
            return false;
        }

        $table = $this->getTableName();
        if ($this->_isNew) {
            return $this->getDb()->insert($table, $this->getAttributes());
        } else {
            return $this->getDb()->update($table, $this->getAttributes(), ['id' => $this->id]);
        }
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

    /**
     * Возвращает публичные атрибуты модели
     *
     * @return array
     */
    public function getAttributes()
    {
        $ref        = new \ReflectionClass($this);
        $attributes = [];
        foreach ($ref->getProperties(\ReflectionProperty::IS_PUBLIC) as $field) {
            $field              = $field->getName();
            $attributes[$field] = $this->$field;
        }

        return $attributes;
    }

    /**
     * @return bool
     */
    private function beforeSave()
    {
        return true;
    }
}
