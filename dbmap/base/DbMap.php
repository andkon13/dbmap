<?php
/**
 * Created by PhpStorm.
 * User: andkon
 * Date: 07.04.14
 * Time: 0:01
 */

namespace dbmap\base;

use dbmap\fields\Id;

/**
 * Class DbMap
 *
 * @package dbmap\base
 */
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
     * Связь многие ко многим [self::MANY_MANY, 'RelClass', 'crossTable', 'masterIdField', 'relIdField']
     * master->slaves[] (t.id = crossTab.masterId and crossTab.relId = re.id).
     * Для корректной работы необходим уникальный индекс на два поля crossTab.masterId и crossTab.relId
     */
    const MANY_MANY = 3;

    static private $with = [];

    /**
     * Будут ли произведена попытка сохранить данные при выгрузке объекта (__destruct)
     *
     * @var bool
     */
    public $autoSaveChange = false;
    protected $relations = [];

    /** @var bool */
    private $isNew = true;

    /**
     * @return boolean
     */
    public function getIsNew()
    {
        return $this->isNew;
    }

    /**
     * Сохранять ли связи при сохранении модели
     *
     * @var bool
     */
    public $saveRelations = false;

    /** @var array */
    private $initAttrubutes = [];

    /** @var array */
    private $errors = [];

    protected static $fields;

    /**
     *
     */
    public function __construct()
    {
        $this->isNew = (empty($this->id));
    }

    /**
     * @return array
     */
    private function getFields()
    {
        if (!static::$fields) {
            $query          = 'SHOW COLUMNS FROM `' . static::getTableName() . '`';
            $res            = Pdo::getInstance()->getResult($query);
            $res            = $res->fetchAll(\PDO::FETCH_ASSOC);
            if (!$res || !count($res)) {
                throw new \Exception('table not exist');
            }

            static::$fields = [];
            foreach ($res as $row) {
                static::$fields[] = $row['Field'];
            }
        }

        return static::$fields;
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
     * Возвращает публичные атрибуты модели
     *
     * @return array
     */
    public function getAttributes()
    {
        $attributes = [];
        $fields     = $this->getFields();
        foreach ($fields as $field) {
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
     * @return static[]
     */
    public static function findAll($limit = 100, $offset = 0)
    {
        $sql   = new QueryBuilder(static::class);
        $param = [];
        if ($limit) {
            $sql->limit((int)$offset . ', ' . (int)$limit);
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
     * @return static[]
     */
    public static function findBySql($sql, $param = array())
    {
        /** @var DbMap $models */
        $result = self::getDb()->getResult($sql, $param);
        /** @var static|string $class */
        $class = static::class;
        /** @var DbMap[] $models */
        $models = $result->fetchAll(\PDO::FETCH_CLASS, $class);
        if (!empty(static::getWith())) {
            $relModels = [];
            $relations = $class::relations();
            foreach (self::getWith() as $relName) {
                $relClass = $relName;
                $query    = new QueryBuilder($relClass);
                if (!in_array($relations[$relName][0], [self::BELONG_TO, self::MANY_MANY])) {
                    $keys = self::getKeys($models);
                    $query->where($relations[$relName][2] . ' in (' . implode(', ', array_keys($keys)) . ')');
                } elseif ($relations[$relName][0] == self::MANY_MANY) {
                    $keys = self::getKeys($models);
                    $query
                        ->select('t.*, rel.' . $relations[$relName][3] . ' as ' . $relations[$relName][2])
                        ->addJoin(
                            'join ' . $relations[$relName][2] . ' rel on rel.' . $relations[$relName][4] . ' = t.id'
                        )
                        ->where('rel.' . $relations[$relName][3] . ' in (' . implode(',', array_keys($keys)) . ')');
                } else {
                    $keys = self::getKeys($models, $relations[$relName][2]);
                    $query->where($relations[$relName][2] . ' in (' . implode(', ', array_keys($keys)) . ')');
                }

                $relModels[$relName] = self::getDb()
                    ->getResult($query->getQuery())
                    ->fetchAll(\PDO::FETCH_CLASS, $relClass);
            }

            foreach ($relModels as $relName => $relClass) {
                if (count($relClass)) {
                    $field = $relations[$relName][2];
                    foreach ($relClass as $class) {
                        $key = $keys[$class->$field];
                        if (!in_array($relations[$relName][0], [self::HAS_MANY, self::MANY_MANY])) {
                            $models[$key]->relations[$relName] = $class;
                        } else {
                            if ($relations[$relName][0] == self::MANY_MANY) {
                                unset($class->$field);
                            }

                            $models[$key]->relations[$relName][] = $class;
                        }
                    }
                } else {
                    foreach ($models as $key => $model) {
                        if (!in_array($relations[$relName][0], [self::HAS_MANY, self::MANY_MANY])) {
                            $models[$key]->relations[$relName] = null;
                        } else {
                            $models[$key]->relations[$relName] = [];
                        }
                    }
                }
            }
        }

        if (count($models)) {
            array_map(function (&$model) {
                /** @var DbMap $model */
                $model->initAttrubutes = $model->getAttributes();
            },
                $models);
        }

        return $models;
    }

    /**
     * @throws \Exception
     * @return Pdo
     */
    public static function getDb()
    {
        return Pdo::getInstance();
    }

    /**
     * @return array
     */
    public static function getWith()
    {
        return self::$with;
    }

    /**
     * @param DbMap[] $models
     * @param string  $field
     *
     * @return array
     */
    private static function getKeys($models, $field = 'id')
    {
        $keys = [];
        foreach ($models as $key => $model) {
            $keys[$model->$field] = $key;
        }

        return $keys;
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
        $class = static::class;
        $class = new $class();
        foreach ($relations as $relName) {
            if (array_key_exists($relName, $class::relations())) {
                self::$with[] = $relName;
            }
        }

        self::$with = array_unique(self::$with);

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
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        if (array_key_exists($name, static::relations())) {
            return static::getRelation($name);
        }

        return false;
    }

    /**
     * @param $name
     * @param $value
     *
     * @return void
     * @throws \Exception
     */
    public function __set($name, $value)
    {
        if (array_key_exists($name, static::relations())) {
            $this->setRelation($name, $value);
        }
    }

    /**
     * Возвращает связанные модели
     *
     * @param string $relation_name ися связи
     *
     * @return DbMap|DbMap[]
     * @throws \Exception
     */
    private function getRelation($relation_name)
    {
        if (array_key_exists($relation_name, $this->relations)) {
            return $this->relations[$relation_name];
        }

        $relation = static::relations()[$relation_name];
        /** @var DbMap $class */
        $class = $relation[1];
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
            case self::MANY_MANY:
                $sql    = '
                    select t.* from ' . $class::getTableName() . ' t
                    join ' . $relation[2] . ' rel on t.id=rel.' . $relation[4] . '
                    where rel.' . $relation[3] . ' = ?
                ';
                $result = $class::findBySql($sql, [$this->id]);
                break;
            default:
                throw new \Exception('Wrong relation type. 0_o');
        }

        if (!array_key_exists($relation_name, $this->relations)) {
            $this->relations[$relation_name] = [];
        }

        $this->relations[$relation_name] = $result;

        return $result;
    }

    /**
     * return table name
     *
     * @return string
     */
    abstract static public function getTableName();

    /**
     * @param $name
     * @param $value
     *
     * @throws \Exception
     * @return void
     */
    private function setRelation($name, $value)
    {
        /** @var DbMap $class */
        $class = static::relations()[$name][1];
        if (!$value instanceof DbMap && is_int($value)) {
            $value = $class::findById($value);
        }

        if (!$value instanceof DbMap) {
            throw new \Exception('Model ' . $class . '->id = ' . $value . ' not exist');
        }

        $this->saveRelations = true;
        $field               = static::relations()[$name][2];
        switch (static::relations()[$name][0]) {
            case self::HAS_ONE:
                $this->relations[$name] = $value;
                if (!$this->isNew) {
                    $value->$field = $this->id;
                }
                break;
            case self::HAS_MANY:
                $this->relations[$name][] = $value;
                if (!$this->isNew) {
                    $value->$field = $this->id;
                }
                break;
            case self::BELONG_TO:
                $this->relations[$name] = $value;
                if (!$this->isNew) {
                    $this->$field = $value->id;
                }
                break;
            case self::MANY_MANY:
                $this->relations[$name][] = $value;
                break;
        }
    }

    /**
     * Деструктор
     */
    public function __destruct()
    {
        if ($this->autoSaveChange) {
            $diff = array_diff($this->initAttrubutes, $this->getAttributes());
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

        try {
            $table = static::getTableName();
            if ($this->isNew) {
                $save     = static::getDb()->insert($table, $this->getAttributes());
                $this->id = $save;
            } else {
                $save = static::getDb()->update($table, $this->getAttributes(), ['id' => $this->id]);
            }

            if ($this->saveRelations) {
                $this->saveRelations();
            }
        } catch (\Exception $e) {
            $save = false;
        }

        $this->afterSave();
        $this->initAttrubutes = $this->getAttributes();

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

        $attributes   = $this->getAttributes();
        $this->errors = [];
        $result       = true;
        foreach ($attributes as $field => $value) {
            $validator_func = $field . 'Validator';
            if (method_exists($this, $validator_func)) {
                $field_result       = $this->$validator_func($value);
                $result             = ($result && $field_result);
                $attributes[$field] = $value;
            }
        }

        $this->setAttributes($attributes);

        return $result;
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function saveRelations()
    {
        foreach ($this->relations as $name => $rels) {
            if (is_array($rels)) {
                /** @var DbMap[] $rels */
                foreach ($rels as $rel) {
                    $rel->save();
                    if (static::relations()[$name][0] == self::MANY_MANY) {
                        $sql = '
                            insert ignore ' . static::relations()[$name][2]
                            . ' (' . static::relations()[$name][3] . ', ' . static::relations()[$name][4]
                            . ') values (:this_id, :rel_id)';
                        self::getDb()->execute(
                            $sql,
                            [
                                ':this_id' => $this->id,
                                ':rel_id'  => $rel->id,
                            ]
                        );
                    }
                }
            } else {
                /** @var DbMap $rels */
                $rels->save();
            }
        }
    }

    /**
     * Возвращает ошибки модели
     *
     * @param string|null $field название поля по которому вернуть ошибки или возвращает ошибки всех полей
     *
     * @return array|null
     */
    public function getErrors($field = null)
    {
        if ($field) {
            return ($this->hasErrors($field)) ? $this->errors[$field] : null;
        }

        return ($this->hasErrors()) ? $this->errors : [];
    }

    /**
     * Проверяет есть ли ошибки
     *
     * @param string|null $field имя проверяемого поля
     *
     * @return bool
     */
    public function hasErrors($field = null)
    {
        if ($field) {
            return isset($this->errors[$field]);
        }

        return 0 !== count($this->errors);
    }

    /**
     * Добавляет ошибки
     *
     * @param string $field
     * @param string $error
     *
     * @return void
     */
    protected function addError($field, $error)
    {
        if (!array_key_exists($field, $this->errors)) {
            $this->errors[$field] = [];
        }

        $this->errors[$field][] = $error;
    }

    /**
     * Удаляет конкретную запись
     *
     * @return bool
     * @throws \Exception
     */
    public function delete()
    {
        if (!$this->getIsNew()) {
            return Pdo::getInstance()
                ->execute('delete from `' . static::getTableName() . '` where id=:id', [':id' => $this->id]);
        }

        return true;
    }
}
