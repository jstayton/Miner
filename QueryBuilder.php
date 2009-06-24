<?php

  class QueryBuilder {

    const INNER_JOIN = "INNER JOIN";
    const LEFT_JOIN = "LEFT JOIN";
    const RIGHT_JOIN = "RIGHT JOIN";

    const LOGICAL_AND = "AND";
    const LOGICAL_OR = "OR";

    const ORDER_BY_ASC = "ASC";
    const ORDER_BY_DESC = "DESC";

    private $option;
    private $select;
    private $fromTable;
    private $join;
    private $where;
    private $groupBy;
    private $having;
    private $orderBy;
    private $limit;

    public function __construct() {
      $this->option = array();
      $this->select = array();
      $this->join = array();
      $this->where = array();
      $this->groupBy = array();
      $this->having = array();
      $this->orderBy = array();
      $this->limit = array();
    }

    public function addOption($option) {
      $this->option[] = $option;

      return $this;
    }

    public function addSelect($column) {
      $this->select[] = $column;

      return $this;
    }

    public function mergeSelectInto(QueryBuilder $QueryBuilder) {
      foreach ($this->option as $currentOption) {
        $QueryBuilder->addOption($currentOption);
      }

      foreach ($this->select as $currentSelect) {
        $QueryBuilder->addSelect($currentSelect);
      }

      return $QueryBuilder;
    }

    public function getSelectString() {
      $select = "";

      if (!empty($this->option)) {
        $select .= implode(' ', $this->option) . " ";
      }

      $select .= implode(', ', $this->select);

      return $select;
    }

    public function setFromTable($table) {
      $this->fromTable = $table;

      return $this;
    }

    public function getFromTable() {
      return $this->fromTable;
    }

    public function addJoin($table, Criteria $Criteria = null, $type = self::INNER_JOIN) {
      $this->join[] = array('table'    => $table,
                            'Criteria' => $Criteria,
                            'type'     => $type);

      return $this;
    }

    public function mergeJoinInto(QueryBuilder $QueryBuilder) {
      foreach ($this->join as $currentJoin) {
        $QueryBuilder->addJoin($currentJoin['table'], $currentJoin['Criteria'], $currentJoin['type']);
      }

      return $QueryBuilder;
    }

    public function getJoinString() {
      $join = "";

      foreach ($this->join as $currentJoin) {
        $join .= " " . $currentJoin['type'] . " " . $currentJoin['table'];

        if (isset($currentJoin['Criteria'])) {
          $join .= " ON " . $currentJoin['Criteria']->getCriteria();
        }
      }

      $join = trim($join);

      return $join;
    }

    public function getFromString() {
      $from = "";

      if (isset($this->fromTable)) {
        $from .= $this->fromTable . " " . $this->getJoinString();
      }

      return $from;
    }

    public function addWhereCriteria(Criteria $Criteria, $connector = self::LOGICAL_AND) {
      $this->where[] = array('Criteria'  => $Criteria,
                             'connector' => $connector);

      return $this;
    }

    public function mergeWhereInto(QueryBuilder $QueryBuilder) {
      foreach ($this->where as $currentWhere) {
        $QueryBuilder->addWhereCriteria($currentWhere['Criteria'], $currentWhere['connector']);
      }

      return $QueryBuilder;
    }

    public function getWhereString() {
      $where = "";

      foreach ($this->where as $i => $currentWhere) {
        if ($i != 0) {
          $where .= " " . $currentWhere['connector'] . " ";
        }

        $where .= $currentWhere['Criteria']->getCriteria();
      }

      return $where;
    }

    public function addGroupBy($column, $order = self::ORDER_BY_ASC) {
      $this->groupBy[] = array('column' => $column,
                               'order'  => $order);

      return $this;
    }

    public function mergeGroupByInto(QueryBuilder $QueryBuilder) {
      foreach ($this->groupBy as $currentGroupBy) {
        $QueryBuilder->addGroupBy($currentGroupBy['column'], $currentGroupBy['order']);
      }

      return $QueryBuilder;
    }

    public function getGroupByString() {
      $groupBy = "";

      foreach ($this->groupBy as $currentGroupBy) {
        $groupBy .= $currentGroupBy['column'] . " " . $currentGroupBy['order'] . ", ";
      }

      $groupBy = substr($groupBy, 0, -2);

      return $groupBy;
    }

    public function addHavingCriteria(Criteria $Criteria, $connector = self::LOGICAL_AND) {
      $this->having[] = array('Criteria'  => $Criteria,
                              'connector' => $connector);

      return $this;
    }

    public function mergeHavingInto(QueryBuilder $QueryBuilder) {
      foreach ($this->having as $currentHaving) {
        $QueryBuilder->addHavingCriteria($currentHaving['Criteria'], $currentHaving['connector']);
      }

      return $QueryBuilder;
    }

    public function getHavingString() {
      $having = "";

      foreach ($this->having as $i => $currentHaving) {
        if ($i != 0) {
          $having .= " " . $currentHaving['connector'] . " ";
        }

        $having .= $currentHaving['Criteria']->getCriteria();
      }

      return $having;
    }

    public function addOrderBy($column, $order = self::ORDER_BY_ASC) {
      $this->orderBy[] = array('column' => $column,
                               'order'  => $order);

      return $this;
    }

    public function mergeOrderByInto(QueryBuilder $QueryBuilder) {
      foreach ($this->orderBy as $currentOrderBy) {
        $QueryBuilder->addOrderBy($currentOrderBy['column'], $currentOrderBy['order']);
      }

      return $QueryBuilder;
    }

    public function getOrderByString() {
      $orderBy = "";

      foreach ($this->orderBy as $currentOrderBy) {
        $orderBy .= $currentOrderBy['column'] . " " . $currentOrderBy['order'] . ", ";
      }

      $orderBy = substr($orderBy, 0, -2);

      return $orderBy;
    }

    public function setLimit($limit, $offset = 0) {
      $this->limit['offset'] = $offset;
      $this->limit['limit'] = $limit;

      return $this;
    }

    public function getLimit() {
      return $this->limit['limit'];
    }

    public function getLimitOffset() {
      return $this->limit['offset'];
    }

    public function getLimitString() {
      $limit = "";

      if (!empty($this->limit)) {
        $limit .= $this->limit['offset'] . ", " . $this->limit['limit'];
      }

      return $limit;
    }

    public function mergeInto(QueryBuilder $QueryBuilder, $overwriteLimit = true) {
      $this->mergeSelectInto($QueryBuilder);
      $this->mergeJoinInto($QueryBuilder);
      $this->mergeWhereInto($QueryBuilder);
      $this->mergeGroupByInto($QueryBuilder);
      $this->mergeHavingInto($QueryBuilder);
      $this->mergeOrderByInto($QueryBuilder);

      if ($overwriteLimit && !empty($this->limit)) {
        $QueryBuilder->setLimit($this->limit['limit'], $this->limit['offset']);
      }

      return $QueryBuilder;
    }

    public function getQuery() {
      $query = "";

      if (!empty($this->select)) {
        $query .= "SELECT " . $this->getSelectString();

        if (isset($this->fromTable)) {
          $query .= " FROM " . $this->getFromString();
        }

        if (!empty($this->where)) {
          $query .= " WHERE " . $this->getWhereString();
        }

        if (!empty($this->groupBy)) {
          $query .= " GROUP BY " . $this->getGroupByString();
        }

        if (!empty($this->having)) {
          $query .= " HAVING " . $this->getHavingString();
        }

        if (!empty($this->orderBy)) {
          $query .= " ORDER BY " . $this->getOrderByString();
        }

        if (!empty($this->limit)) {
          $query .= " LIMIT " . $this->getLimitString();
        }
      }

      return $query;
    }

  }

?>