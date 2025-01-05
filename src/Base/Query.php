<?php

namespace AstraTech\DataForge\Base;

use \Illuminate\Support\Arr;

class Query extends ClassObject
{
    protected $_name;

    protected $from;

    protected $join = [];

    protected $where = [];

    protected $whereGroup = [];

    protected $select = [];

    protected $group;

    protected  $orderBy = '';

    protected  $order = '';

    protected $query;

    protected $select_type = 'list';

    public function __construct($name)
    {
        $this->_name = 'SQL:'.$name;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function assignKeys($input)
    {
        $keys = ['select_type', 'limit', 'page'];
        foreach ($keys as $key) {
            if (Arr::has($input, $key))
                $this->{$key} = $input[$key];
        }
    }

    function assign($query)
    {
        $this->query = $query;
        return $this;
    }

    function from($str)
    {
        $this->set('from', $str);
        return $this;
    }

    function select($name, $value)
    {
        $this->select[$name] = $value;
        return $this;
    }

    function join($str)
    {
        if ($str != '')
            $this->join[] = 'JOIN '.$str;
        return $this;
    }

    function inner($str)
    {
        if ($str != '')
            $this->join[] = 'INNER JOIN '.$str;
        return $this;
    }

    function left($str)
    {
        if ($str != '')
            $this->join[] = 'LEFT JOIN '.$str;
        return $this;
    }

    function filter($str, $required = true)
    {
        if ($str != '')
            $this->where[] = ['condition' => $str, 'required' => $required];

        return $this;
    }

    function filterOptional($str)
    {
        return $this->filter($str, false);
    }

    function filterAnyOneRequired($type, $array)
    {
        $this->whereGroup[$type] = $array;
        return $this;
    }

    function group($str)
    {
        $this->set('group', $str);
        return $this;
    }
    public function order($field, $direction = 'asc')
    {
        $this->set('orderBy', $field);

        $direction = strtolower(trim($direction));
        if (in_array($direction,  array('asc', 'desc')))
            $this->set('order', strtoupper($direction));

        return $this;
    }

    public function getSelect()
    {
        $type = trim($this->select_type);
        if ($type && !in_array($type, array_keys($this->select)))
            $this->raiseError($this->_name.' - Invalid select type ('.$type.')!');

        return $this->select[$type];
    }

	public function getGroup()
	{
		if ($this->select_type == 'total')
			return '';

		return $this->group;
	}

	public function getOrder()
	{
		if ($this->select_type == 'total')
			return '';

		return trim($this->orderBy);
	}

    public function getLimit()
	{
        $limit = intval($this->limit ?? 0);
		if ($this->select_type == 'total' || !$limit)
			return '';

        $page  = intval($this->page ?? 0);
        $from = $page > 0 ? ($page - 1) * $limit : 0;

		return $from.','.$limit;
	}

    public function __toString()
    {
		if ($this->query)
			return $this->query;

        // Build the SQL query string.
        $query = ["SELECT"];
        $query[] = "\t".$this->getSelect();
        $query[] = $this->from;

        foreach ($this->join as $join)
            $query[] = $join;

        $this->where = array_values($this->where);
        foreach ($this->where as $key => $where)
        {
            if (!$key)
                $query[] = 'WHERE';

            $query[] = $key ? "\tAND (". $where['condition'] .")" : "\t(".$where['condition'].")";
        }

		$group = $this->getGroup();
        if ($group)
            $query[] = 'GROUP BY '.$group;

		$order = $this->getOrder();
        if ($order)
            $query[] = 'ORDER BY '.$order;

		$limit = $this->getLimit();
		if ($limit)
			$query[] = 'LIMIT '.$limit;

        return implode(" \n", $query);
    }

    public function bind($data)
    {
		$failed = [];
		if ($this->query) {
			$this->query = \Factory()->replaceConstant($this->query, $data, $failed, false);
			if (!$this->query && $failed)
				$this->raiseError($this->_name.' - required inputs ('.implode(", ", $failed).') missing!');
			return;
		}

        foreach ($this->where AS $key => $where)
        {
            $failed = [];
            $str = \Factory()->replaceConstant($where['condition'], $data, $failed, true);
            if ($str && !$failed) {
                $this->where[$key]['condition'] = $str;
                continue;
            }

            if ($where['required'] && $failed)
                $this->raiseError($this->_name.' - required inputs ('.implode(", ", $failed).') missing!');

            unset($this->where[$key]);
        }

        foreach ($this->whereGroup as $key => $conditions)
        {
            $added = false;
            foreach  ($conditions as $i => $condition)
            {
                $failed = [];
                $str = \Factory()->replaceConstant($condition, $data, $failed, true);
                if (!$str || $failed)
                    continue;

                $added = true;
                $this->where[]['condition'] = $str;
            }

            if (!$added)
                $this->raiseError($this->_name.' - group ('.$key.') no more conditions matched!');
        }

        $failed = [];
        $this->orderBy = \Factory()->replaceConstant($this->orderBy.' '.$this->order, $data, $failed, false);
        if ($failed)
            $this->orderBy = '';
    }
}
