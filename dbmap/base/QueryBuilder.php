<?php
/**
 * Created by PhpStorm.
 * User: andkon
 * Date: 17.04.14
 * Time: 15:44
 */

namespace dbmap\base;

class QueryBuilder
{
    private $_select = 't.*';
    private $_where = '';
    private $_join = '';
    private $_limit = '';
    private $_group = '';
    private $_hawing = '';
    private $_table;
    /** @var \dbmap\base\DbMap */
    private $_class;

    /**
     * @param DbMap $class
     */
    function __construct($class)
    {
        $this->_class = $class;
        $this->_table = $class::getTableName();
    }

    /**
     * Возвращает sql запрос
     *
*@return string
     */
    public function getQuery()
    {
        $query = 'select ' . $this->_select . ' from ' . $this->_table . ' t ';
        if (!empty($this->_join)) {
            $query .= $this->_join;
        }

        if (!empty($this->_where)) {
            $query .= ' where ' . $this->_where;
        }

        if (!empty($this->_group)) {
            $query .= ' group by ' . $this->_group;
        }

        if (!empty($this->_hawing)) {
            $query .= ' having ' . $this->_hawing;
        }

        if (!empty($this->_limit)) {
            $query .= ' limit ' . $this->_limit;
        }

        return $query;
    }

    /**
     * Добавляет жоины
     *
     * @param string $join строка будет вставлена в join


*
*@return $this
     */
    public function addJoin($join)
    {
        $this->_join .= $join;

        return $this;
    }

    /**
     * Параметры группировки
     *
     * @param string $group
     *
     * @return $this
     */
    public function group($group)
    {
        $this->_group = $group;

        return $this;
    }

    /**
     * @param string $select
     *
     * @return $this
     */
    public function select($select = 't.*')
    {
        $select        = ($select == '*') ? 't.*' : $select;
        $this->_select = $select;

        return $this;
    }

    /**
     * @param string $where
     *
     * @return $this
     */
    public function where($where)
    {
        $this->_where = $where;

        return $this;
    }

    /**
     * Добавление параметров выборки
     *
     * @param string $where
     * @param string $connention and|or
     *
     * @return $this
     */
    public function addWhere($where, $connention = 'and')
    {
        if (!empty($this->_where)) {
            $this->_where .= $connention;
        }

        $this->_where .= $where;

        return $this;
    }

    /**
     * @param string $limit
     *
     * @return $this
     */
    public function limit($limit)
    {
        $this->_limit = $limit;

        return $this;
    }

    /**
     * @param string $having
     *
     * @return $this
     */
    public function having($having)
    {
        $this->_hawing = $having;

        return $this;
    }
}
