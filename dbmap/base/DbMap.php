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
    use Id, Dummy;

    /**
     * Связь один к одному [self::HAS_ONE, 'RelClass', 'refField'] master->slave (t.id = rel.t_id)
     */
    const HAS_ONE = 1;

    /**
     * Связь один к одному [self::BELONG_TO, 'RelClass', 'thisField'] slave->master (rel.t_id = t.id)
     */
    const BELONG_TO = 0;

    /**
     * Связь один ко многим [self::HAS_MANY, 'RelClass', 'refField']  master->slaves[] (t.id = rel.t_id)
     */
    const HAS_MANY = 2;

    /**
     * Будут ли произведена попытка сохранить данные при выгрузке объекта (__destruct)
     *
     * @var bool
     */
    public $autoSaveChange = false;
    public $_relations = [];

    /** @var bool|Pdo */
    static private $_db = false;
    static private $_with = [];

    /** @var bool */
    private $_isNew = true;

    /** @var array */
    private $_initAttrubutes = [];

    /**
     * @param bool  $isNew
     * @param array $attributes
     */
    function __construct($isNew = false, $attributes = [])
    {
        $this->_isNew = $isNew;
        if (!empty($attributes)) {
            $this->_initAttrubutes = $attributes;
            $this->setAttributes($attributes);
        } else {
            $this->_initAttrubutes = $this->getAttributes();
        }
    }

    /**
     * return table name
     *
     * @return string
     */
    abstract static public function getTableName();

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
     * Возвращает все обекты модели
     *
     * @param int $limit  Лимит записей
     * @param int $offset сколько записей пропустить
     *
     * @return DbMap[]
     */
    public static function findAll($limit = 100, $offset = 0)
    {
        $sql   = new QueryBuilder(get_called_class());
        $param = [];
        if ($limit) {
            $sql->limit(intval($offset) . ', ' . intval($limit));
        }

        return self::findBySql($sql->getQuery(), $param);
    }

    /**
     * Возвращает массив моделей по запросу
     *
     * @param string $sql   запрос
     * @param array  $param параметры для запроса
     *
     * @throws \Exception
     * @return DbMap[]
     */
    public static function findBySql($sql, $param = array())
    {
        /** @var DbMap $models */
        $result = self::getDb()->getResult($sql, $param);
        /** @var DbMap $models */
        $class = get_called_class();
        /** @var DbMap[] $models */
        $models = $result->fetchAll(\PDO::FETCH_CLASS, $class);
        if (!empty(self::getWith())) {
            $relModels = [];
            $relations = $class::relations();
            foreach (self::getWith() as $relName) {
                if (class_exists($relName)) {
                    $relClass = $relName;
                } else {
                    $relClass = $class::getNameSpace($class) . '\\' . $relName;
                }

                $query = new QueryBuilder($relClass);
                if ($relations[$relName][0] != self::BELONG_TO) {
                    $keys = self::_getKeys($models);
                    $query->where($relations[$relName][2] . ' in (' . implode(', ', array_keys($keys)) . ')');
                } else {
                    $keys = self::_getKeys($models, $relations[$relName][2]);
                    $query->where($relations[$relName][2] . ' in (' . implode(', ', array_keys($keys)) . ')');
                }

                $relModels[$relName] = self::getDb()->getResult($query->getQuery(false))->fetchAll(\PDO::FETCH_CLASS, $relClass);
            }
        }

        foreach ($relModels as $relName => $relClass) {
            $field = $relations[$relName][2];
            foreach ($relClass as $class) {
                $key = $keys[$class->$field];
                if ($relations[$relName][0] !== self::HAS_MANY) {
                    $models[$key]->_relations[$relName] = $class;
                } else {
                    $models[$key]->_relations[$relName][] = $class;
                }
            }
        }

        return $models;
    }

    /**
     * @throws \Exception
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
     * Добавляет какие связи будут в выборке при поиске
     *
     * @param array|string $relations название связи
     *
     * @return DbMap
     */
    public static function with($relations = array())
    {
        if (!is_array($relations)) {
            $relations = [$relations];
        }

        /** @var DbMap $class */
        $class = get_called_class();
        $class = new $class();
        foreach ($relations as $relName) {
            if (array_key_exists($relName, $class::relations())) {
                self::$_with[] = $relName;
            }
        }

        self::$_with = array_unique(self::$_with);

        return $class;
    }

    /**
     * Возвращает связи
     *
     * @return array
     */
    public static function relations()
    {
        return array();
    }

    /**
     * @return array
     */
    public static function getWith()
    {
        return self::$_with;
    }

    /**
     * @param DbMap[] $models
     *
     * @param string  $field
     *
     * @return array
     */
    private static function _getKeys($models, $field = 'id')
    {
        $keys = [];
        foreach ($models as $key => $model) {
            $keys[$model->$field] = $key;
        }

        return $keys;
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
     * Возвращает связанные модели
     *
     * @param string $relation_name ися связи
     *
     * @return DbMap|DbMap[]
     * @throws \Exception
     */
    private function _getRelation($relation_name)
    {
        if (array_key_exists($relation_name, $this->_relations)) {
            return $this->_relations[$relation_name];
        }

        $relation = $this->relations()[$relation_name];
        /** @var DbMap $class */
        $class = $relation[1];
        $class = (class_exists($class)) ? $class : $this->getNameSpace($this) . '\\' . $class;
        $field = $relation[2];
        switch ($relation[0]) {
            case self::HAS_MANY:
                $sql    = 'select * from ' . $class::getTableName() . ' where ' . $field . ' = ?';
                $result = $class::findBySql($sql, [$this->id]);
                break;
            case self::HAS_ONE:
                $sql    = 'select * from ' . $class::getTableName() . ' where ' . $field . ' = ?';
                $result = $class::findBySql($sql, [$this->id]);
                $result = (isset($result[0])) ? $result[0] : [];
                break;
            case self::BELONG_TO:
                $sql    = 'select * from ' . $class::getTableName() . ' where id = ?';
                $result = $class::findBySql($sql, [$this->$field]);
                $result = (isset($result[0])) ? $result[0] : [];
                break;
            default:
                throw new \Exception('Wrong relation type. 0_o');
        }

        return $result;
    }

    /**
     * Возвращает неймспейс объекта
     *
     * @param mixed $object
     *
     * @return string
     */
    public static function getNameSpace($object)
    {
        $reflect = new \ReflectionClass($object);

        return $reflect->getNamespaceName();
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

        $this->afterSave();
        $this->_initAttrubutes = $this->getAttributes();

        return $save;
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
                $result             = ($result && $this->$validator_func($value));
                $attributes[$field] = $value;
            }
        }

        return $result;
    }
}
