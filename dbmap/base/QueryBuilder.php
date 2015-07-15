<?php
/**
 * Created by PhpStorm.
 * User: andkon
 * Date: 17.04.14
 * Time: 15:44
 */

namespace dbmap\base;

/**
 * Class QueryBuilder
 *
 * @package dbmap\base
 */
class QueryBuilder
{
    private $select = 't.*';
    private $where = '';
    private $join = '';
    private $limit = '';
    private $group = '';
    private $hawing = '';
    private $table;
    /** @var \dbmap\base\DbMap */
    private $class;

    /**
     * @param DbMap $class
     */
    public function __construct($class)
    {
        $this->class = $class;
        $this->table = $class::getTableName();
    }

    /**
     * Возвращает sql запрос
     *
     * @return string
     */
    public function getQuery()
    {
        $query = 'select ' . $this->select . ' from ' . $this->table . ' t ';
        if (!empty($this->join)) {
            $query .= $this->join;
        }

        if (!empty($this->where)) {
            $query .= ' where ' . $this->where;
        }

        if (!empty($this->group)) {
            $query .= ' group by ' . $this->group;
        }

        if (!empty($this->hawing)) {
            $query .= ' having ' . $this->hawing;
        }

        if (!empty($this->limit)) {
            $query .= ' limit ' . $this->limit;
        }

        return $query;
    }

    /**
     * Добавляет жоины
     *
     * @param string $join строка будет вставлена в join
     *
     * @return static
     */
    public function addJoin($join)
    {
        $this->join .= $join;

        return $this;
    }

    /**
     * Параметры группировки
     *
     * @param string $group
     *
     * @return static
     */
    public function group($group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @param string $select
     *
     * @return static
     */
    public function select($select = 't.*')
    {
        $select        = ($select === '*') ? 't.*' : $select;
        $this->select = $select;

        return $this;
    }

    /**
     * @param string $where
     *
     * @return static
     */
    public function where($where)
    {
        $this->where = $where;

        return $this;
    }

    /**
     * Добавление параметров выборки
     *
     * @param string $where
     * @param string $connention and|or
     *
     * @return static
     */
    public function addWhere($where, $connention = 'and')
    {
        if (!empty($this->where)) {
            $this->where .= $connention;
        }

        $this->where .= $where;

        return $this;
    }

    /**
     * @param string $limit
     *
     * @return static
     */
    public function limit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @param string $having
     *
     * @return static
     */
    public function having($having)
    {
        $this->hawing = $having;

        return $this;
    }
}
