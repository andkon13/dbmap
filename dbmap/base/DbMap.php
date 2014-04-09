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

    /**
     * Связь один к одному master->slave (t.id = rel.t_id)
     */
    const HAS_ONE = 1;

    /**
     * Связь один к одному slave->master (rel.t_id = t.id)
     */
    const BELONG_TO = 0;

    /**
     * Связь один ко многим master->slaves[] (t.id = rel.t_id)
     */
    const HAS_MANY = 2;

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
     * Сохраняет модель
     *
     * @return bool|string
     */
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
            $save     = $this->getDb()->insert($table, $this->getAttributes());
            $this->id = $save;
        } else {
            $save = $this->getDb()->update($table, $this->getAttributes(), ['id' => $this->id]);
        }

        $this->_initAttrubutes = $this->getAttributes();

        return $save;
    }

    /**
     * Устанавливает атрибуты
     *
     * @param $attributes
     *
     * @return $this
     */
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
     * Возвращает связи
     *
     * @return array
     */
    public function relations()
    {
        return array();
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    function __get($name)
    {
        if (array_key_exists($name, $this->relations())) {
            return $this->_getRelation($name);
        }

        return false;
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
    private function beforeSave()
    {
        return true;
    }

    /**
     * Деструктор
     */
    public function __destruct()
    {
        if ($this->autoSaveChange) {
            $diff = array_diff($this->_initAttrubutes, $this->getAttributes());
            if (count($diff)) {
                $this->save();
            }
        }
    }

    /**
     * Возвращает мвязанные модели
     *
     * @param string $relation_name ися связи
     *
     * @return DbMap|DbMap[]
     * @throws \Exception
     */
    private function _getRelation($relation_name)
    {
        $relation = $this->relations($relation_name);
        /** @var DbMap $class */
        $class = $relation[1];
        $field = $relation[2];
        switch ($relation[0]) {
            case self::HAS_MANY:
                $sql    = 'select * from ' . $class::getTableName() . ' where ' . $field . ' = ?';
                $result = $class::bySql($sql, [$this->id]);
                break;
            case self::HAS_ONE:
                $sql    = 'select * from ' . $class::getTableName() . ' where ' . $field . ' = ?';
                $result = $class::bySql($sql, [$this->id]);
                $result = (isset($result[0])) ? $result[0] : [];
                break;
            case self::BELONG_TO:
                $sql    = 'select * from ' . $class::getTableName() . ' where id = ?';
                $result = $class::bySql($sql, [$this->$field]);
                $result = (isset($result[0])) ? $result[0] : [];
                break;
            default:
                throw new \Exception('Wrong relation type. 0_o');
        }

        return $result;
    }

    /**
     * Возвращает массив моделей по запросу
     *
     * @param string $sql   запрос
     * @param array  $param параметры для запроса
     *
     * @return DbMap[]
     */
    public static function bySql($sql, $param = array())
    {
        $result = self::getDb()->getResult($sql, $param);
        $class  = get_called_class();
        $models = [];
        if (is_array($result)) {
            foreach ($result as $row) {
                $models[] = new $class(false, $row);
            }
        }

        return $models;
    }
}
