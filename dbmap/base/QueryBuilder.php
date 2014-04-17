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
    private static $_tablesFields = [];
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

    public function getQuery($includeWith = true)
    {
        if ($includeWith) {
            $this->buildQueryWith();
        }

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

    private function buildQueryWith()
    {
        /** @var DbMap $class */
        $class = $this->_class;
        if (!empty($class::getWith())) {
            /** @var DbMap $class */
            $relations = $class::relations();
            foreach ($class::getWith() as $relName) {
                $ns = $class::getNameSpace($class);
                if (class_exists($relName)) {
                    /** @var DbMap $relClass */
                    $relClass = $relName;
                } else if (class_exists($ns . '\\' . $relName)) {
                    $relClass = $ns . '\\' . $relName;
                } else {
                    throw new \Exception('Class ' . $relName . ' not found.');
                }

                $join = ' left join ' . $relClass::getTableName() . ' as ' . $relName . ' on ';
                $this->_select .= ', GROUP_CONCAT(' . $relName . '.id) as ' . $relName;
                switch ($relations[$relName][0]) {
                    case DbMap::HAS_ONE:
                        $join .= 't.id=' . $relName . '.' . $relations[$relName][2];
                        break;
                    case DbMap::HAS_MANY:
                        $join .= 't.id=' . $relName . '.' . $relations[$relName][2];
                        break;
                    case DbMap::BELONG_TO:
                        $join .= 't.' . $relations[$relName][2] . ' = ' . $relName . '.id';
                        break;
                    default:
                        throw new \Exception('Wrong relation type. 0_o');
                }

                $this->addJoin($join);
                $this->group('t.id');
            }
        }
    }

    public function addJoin($join)
    {
        $this->_join .= $join;

        return $this;
    }

    public function group($group)
    {
        $this->_group = $group;

        return $this;
    }

    public function select($select = 't.*')
    {
        $select        = ($select == '*') ? 't.*' : $select;
        $this->_select = $select;

        return $this;
    }

    public function where($where)
    {
        $this->_where = $where;

        return $this;
    }

    public function addWhere($where, $connention = 'and')
    {
        if (!empty($this->_where)) {
            $this->_where .= $connention;
        }

        $this->_where .= $where;

        return $this;
    }

    public function limit($limit)
    {
        $this->_limit = $limit;

        return $this;
    }

    public function having($having)
    {
        $this->_hawing = $having;

        return $this;
    }
}
