<?php

  class QueryBuilder {

    const INNER_JOIN = "INNER JOIN";
    const LEFT_JOIN = "LEFT JOIN";
    const RIGHT_JOIN = "RIGHT JOIN";

    const LOGICAL_AND = "AND";
    const LOGICAL_OR = "OR";

    const EQUALS = "=";
    const NOT_EQUALS = "!=";
    const LESS_THAN = "<";
    const LESS_THAN_OR_EQUAL = "<=";
    const GREATER_THAN = ">";
    const GREATER_THAN_OR_EQUAL = ">=";
    const IN = "IN";
    const NOT_IN = "NOT IN";
    const LIKE = "LIKE";
    const NOT_LIKE = "NOT LIKE";
    const REGEX = "REGEXP";
    const NOT_REGEX = "NOT REGEXP";
    const BETWEEN = "BETWEEN";
    const NOT_BETWEEN = "NOT BETWEEN";
    const IS = "IS";
    const IS_NOT = "IS NOT";

    const ORDER_BY_ASC = "ASC";
    const ORDER_BY_DESC = "DESC";

    const BRACKET_OPEN = "(";
    const BRACKET_CLOSE = ")";

    const NULL = "NULL";
    const BOOLEAN_TRUE = "TRUE";
    const BOOLEAN_FALSE = "FALSE";
    const BOOLEAN_UNKNOWN = "UNKNOWN";

    private $PdoConnection;

    private $option;
    private $select;
    private $from;
    private $join;
    private $where;
    private $groupBy;
    private $having;
    private $orderBy;
    private $limit;

    private $wherePlaceholderValues;
    private $havingPlaceholderValues;

    public function __construct(PDO $PdoConnection) {
      $this->option = array();
      $this->select = array();
      $this->from = array();
      $this->join = array();
      $this->where = array();
      $this->groupBy = array();
      $this->having = array();
      $this->orderBy = array();
      $this->limit = array();

      $this->wherePlaceholderValues = array();
      $this->havingPlaceholderValues = array();

      $this->setPdoConnection($PdoConnection);
    }

    public function setPdoConnection(PDO $PdoConnection) {
      $this->PdoConnection = $PdoConnection;

      return $this;
    }

    public function getPdoConnection() {
      return $this->PdoConnection;
    }

    public function quote($value) {
      return $this->getPdoConnection()->quote($value);
    }

    public function option($option) {
      $this->option[] = $option;

      return $this;
    }

    public function calcFoundRows() {
      return $this->option('SQL_CALC_FOUND_ROWS');
    }

    public function distinct() {
      return $this->option('DISTINCT');
    }

    public function select($column, $alias = null) {
      $this->select[$column] = $alias;

      return $this;
    }

    public function mergeSelectInto(QueryBuilder $QueryBuilder) {
      foreach ($this->option as $currentOption) {
        $QueryBuilder->option($currentOption);
      }

      foreach ($this->select as $currentColumn => $currentAlias) {
        $QueryBuilder->select($currentColumn, $currentAlias);
      }

      return $QueryBuilder;
    }

    public function getSelectString() {
      $select = "";

      if (!empty($this->option)) {
        $select .= implode(' ', $this->option) . " ";
      }

      foreach ($this->select as $currentColumn => $currentAlias) {
        $select .= $currentColumn;

        if (isset($currentAlias)) {
          $select .= " AS " . $currentAlias;
        }

        $select .= ", ";
      }

      $select = substr($select, 0, -2);

      return $select;
    }

    public function from($table, $alias = null) {
      $this->from['table'] = $table;
      $this->from['alias'] = $alias;

      return $this;
    }

    public function getFrom() {
      return $this->from['table'];
    }

    public function getFromAlias() {
      return $this->from['alias'];
    }

    public function join($table, $criteria = null, $type = self::INNER_JOIN, $alias = null) {
      if (is_string($criteria)) {
        $criteria = array($criteria);
      }

      $this->join[] = array('table'    => $table,
                            'criteria' => $criteria,
                            'type'     => $type,
                            'alias'    => $alias);

      return $this;
    }

    public function innerJoin($table, $criteria = null, $alias = null) {
      return $this->join($table, $criteria, self::INNER_JOIN, $alias);
    }

    public function leftJoin($table, $criteria = null, $alias = null) {
      return $this->join($table, $criteria, self::LEFT_JOIN, $alias);
    }

    public function rightJoin($table, $criteria = null, $alias = null) {
      return $this->join($table, $criteria, self::RIGHT_JOIN, $alias);
    }

    public function mergeJoinInto(QueryBuilder $QueryBuilder) {
      foreach ($this->join as $currentJoin) {
        $QueryBuilder->join($currentJoin['table'], $currentJoin['criteria'], $currentJoin['type'],
                            $currentJoin['alias']);
      }

      return $QueryBuilder;
    }

    private function getJoinCriteriaUsingPreviousTable($joinIndex, $table, $column) {
      $previousJoinIndex = $joinIndex - 1;

      if (array_key_exists($previousJoinIndex, $this->join)) {
        $previousTable = $this->join[$previousJoinIndex]['table'];
      }
      else {
        $previousTable = $this->getFrom();
      }

      return $previousTable . "." . $column . " = " . $table . "." . $column;
    }

    public function getJoinString() {
      $join = "";

      foreach ($this->join as $i => $currentJoin) {
        $join .= " " . $currentJoin['type'] . " " . $currentJoin['table'];

        if (isset($currentJoin['alias'])) {
          $join .= " AS " . $currentJoin['alias'];
        }

        if (isset($currentJoin['criteria'])) {
          $join .= " ON ";

          foreach ($currentJoin['criteria'] as $x => $criterion) {
            if ($x != 0) {
              $join .= " " . self::LOGICAL_AND . " ";
            }

            if (strpos($criterion, '=') === false) {
              $join .= $this->getJoinCriteriaUsingPreviousTable($i, $currentJoin['table'], $criterion);
            }
            else {
              $join .= $criterion;
            }
          }
        }
      }

      $join = trim($join);

      return $join;
    }

    public function getFromString() {
      $from = "";

      if (!empty($this->from)) {
        $from .= $this->from['table'];

        if (isset($this->from['alias'])) {
          $from .= " AS " . $this->from['alias'];
        }

        $from .= " " . $this->getJoinString();
      }

      $from = rtrim($from);

      return $from;
    }

    private function openCriteria(array &$criteria, $connector = self::LOGICAL_AND) {
      $criteria[] = array('bracket'   => self::BRACKET_OPEN,
                          'connector' => $connector);

      return $this;
    }

    private function closeCriteria(array &$criteria) {
      $criteria[] = array('bracket'   => self::BRACKET_CLOSE,
                          'connector' => null);

      return $this;
    }

    private function criteria(array &$criteria, $column, $value, $operator = self::EQUALS, $connector = self::LOGICAL_AND) {
      $criteria[] = array('column'    => $column,
                          'value'     => $value,
                          'operator'  => $operator,
                          'connector' => $connector);

      return $this;
    }

    private function orCriteria(array &$criteria, $column, $value, $operator = self::EQUALS) {
      return $this->criteria($criteria, $column, $value, $operator, self::LOGICAL_OR);
    }

    private function criteriaIn(array &$criteria, $column, array $values, $connector = self::LOGICAL_AND) {
      return $this->criteria($criteria, $column, $values, self::IN, $connector);
    }

    private function criteriaNotIn(array &$criteria, $column, array $values, $connector = self::LOGICAL_AND) {
      return $this->criteria($criteria, $column, $values, self::NOT_IN, $connector);
    }

    private function criteriaBetween(array &$criteria, $column, $min, $max, $connector = self::LOGICAL_AND) {
      $value = array($min, $max);

      return $this->criteria($criteria, $column, $value, self::BETWEEN, $connector);
    }

    private function criteriaNotBetween(array &$criteria, $column, $min, $max, $connector = self::LOGICAL_AND) {
      $value = array($min, $max);

      return $this->criteria($criteria, $column, $value, self::NOT_BETWEEN, $connector);
    }

    private function getCriteriaString(array &$criteria, $usePlaceholders = true, array &$placeholderValues = array()) {
      $string = "";

      $useConnector = false;

      foreach ($criteria as $i => $currentCriterion) {
        if (array_key_exists('bracket', $currentCriterion)) {
          if (strcmp($currentCriterion['bracket'], self::BRACKET_OPEN) == 0) {
            if ($useConnector) {
              $string .= " " . $currentCriterion['connector'] . " ";
            }

            $useConnector = false;
          }
          else {
            $useConnector = true;
          }

          $string .= $currentCriterion['bracket'];
        }
        else {
          if ($useConnector) {
            $string .= " " . $currentCriterion['connector'] . " ";
          }

          $useConnector = true;

          switch ($currentCriterion['operator']) {
            case self::BETWEEN:
            case self::NOT_BETWEEN:
              if ($usePlaceholders) {
                $value = "? AND ?";

                $placeholderValues[] = $currentCriterion['value'][0];
                $placeholderValues[] = $currentCriterion['value'][1];
              }
              else {
                $value = $this->quote($currentCriterion['value'][0]) . " " . self::LOGICAL_AND . " " .
                         $this->quote($currentCriterion['value'][1]);
              }

              break;

            case self::IN:
            case self::NOT_IN:
              if ($usePlaceholders) {
                $value = self::BRACKET_OPEN . substr(str_repeat('?, ', count($currentCriterion['value'])), 0, -2) .
                         self::BRACKET_CLOSE;

                $placeholderValues = array_merge($placeholderValues, $currentCriterion['value']);
              }
              else {
                $value = self::BRACKET_OPEN;

                foreach ($currentCriterion['value'] as $currentValue) {
                  $value .= $this->quote($currentValue) . ", ";
                }

                $value  = substr($value, 0, -2);
                $value .= self::BRACKET_CLOSE;
              }

              break;

            case self::IS:
            case self::IS_NOT:
              $value = $currentCriterion['value'];

              break;

            default:
              if ($usePlaceholders) {
                $value = "?";

                $placeholderValues[] = $currentCriterion['value'];
              }
              else {
                $value = $this->quote($currentCriterion['value']);
              }

              break;
          }

          $string .= $currentCriterion['column'] . " " . $currentCriterion['operator'] . " " . $value;
        }
      }

      return $string;
    }

    public function openWhere($connector = self::LOGICAL_AND) {
      return $this->openCriteria($this->where, $connector);
    }

    public function closeWhere() {
      return $this->closeCriteria($this->where);
    }

    public function where($column, $value, $operator = self::EQUALS, $connector = self::LOGICAL_AND) {
      return $this->criteria($this->where, $column, $value, $operator, $connector);
    }

    public function orWhere($column, $value, $operator = self::EQUALS) {
      return $this->orCriteria($this->where, $column, $value, $operator, self::LOGICAL_OR);
    }

    public function whereIn($column, array $values, $connector = self::LOGICAL_AND) {
      return $this->criteriaIn($this->where, $column, $values, $connector);
    }

    public function whereNotIn($column, array $values, $connector = self::LOGICAL_AND) {
      return $this->criteriaNotIn($this->where, $column, $values, $connector);
    }

    public function whereBetween($column, $min, $max, $connector = self::LOGICAL_AND) {
      return $this->criteriaBetween($this->where, $column, $min, $max, $connector);
    }

    public function whereNotBetween($column, $min, $max, $connector = self::LOGICAL_AND) {
      return $this->criteriaNotBetween($this->where, $column, $min, $max, $connector);
    }

    public function mergeWhereInto(QueryBuilder $QueryBuilder) {
      foreach ($this->where as $currentWhere) {
        if (array_key_exists('bracket', $currentWhere)) {
          if (strcmp($currentWhere['bracket'], self::BRACKET_OPEN) == 0) {
            $QueryBuilder->openWhere($currentWhere['connector']);
          }
          else {
            $QueryBuilder->closeWhere();
          }
        }
        else {
          $QueryBuilder->where($currentWhere['column'], $currentWhere['value'],
                               $currentWhere['operator'], $currentWhere['connector']);
        }
      }

      return $QueryBuilder;
    }

    public function getWhereString($usePlaceholders = true) {
      return $this->getCriteriaString($this->where, $usePlaceholders, $this->wherePlaceholderValues);
    }

    public function getWherePlaceholderValues() {
      return $this->wherePlaceholderValues;
    }

    public function groupBy($column, $order = self::ORDER_BY_ASC) {
      $this->groupBy[] = array('column' => $column,
                               'order'  => $order);

      return $this;
    }

    public function mergeGroupByInto(QueryBuilder $QueryBuilder) {
      foreach ($this->groupBy as $currentGroupBy) {
        $QueryBuilder->groupBy($currentGroupBy['column'], $currentGroupBy['order']);
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

    public function openHaving($connector = self::LOGICAL_AND) {
      return $this->openCriteria($this->having, $connector);
    }

    public function closeHaving() {
      return $this->closeCriteria($this->having);
    }

    public function having($column, $value, $operator = self::EQUALS, $connector = self::LOGICAL_AND) {
      return $this->criteria($this->having, $column, $value, $operator, $connector);
    }

    public function orHaving($column, $value, $operator = self::EQUALS) {
      return $this->orCriteria($this->having, $column, $value, $operator, self::LOGICAL_OR);
    }

    public function havingIn($column, array $values, $connector = self::LOGICAL_AND) {
      return $this->criteriaIn($this->having, $column, $values, $connector);
    }

    public function havingNotIn($column, array $values, $connector = self::LOGICAL_AND) {
      return $this->criteriaNotIn($this->having, $column, $values, $connector);
    }

    public function havingBetween($column, $min, $max, $connector = self::LOGICAL_AND) {
      return $this->criteriaBetween($this->having, $column, $min, $max, $connector);
    }

    public function havingNotBetween($column, $min, $max, $connector = self::LOGICAL_AND) {
      return $this->criteriaNotBetween($this->having, $column, $min, $max, $connector);
    }

    public function mergeHavingInto(QueryBuilder $QueryBuilder) {
      foreach ($this->having as $currentHaving) {
        if (array_key_exists('bracket', $currentHaving)) {
          if (strcmp($currentHaving['bracket'], self::BRACKET_OPEN) == 0) {
            $QueryBuilder->openHaving($currentHaving['connector']);
          }
          else {
            $QueryBuilder->closeHaving();
          }
        }
        else {
          $QueryBuilder->having($currentHaving['column'], $currentHaving['value'],
                                $currentHaving['operator'], $currentHaving['connector']);
        }
      }

      return $QueryBuilder;
    }

    public function getHavingString($usePlaceholders = true) {
      return $this->getCriteriaString($this->having, $usePlaceholders, $this->havingPlaceholderValues);
    }

    public function getHavingPlaceholderValues() {
      return $this->havingPlaceholderValues;
    }

    public function orderBy($column, $order = self::ORDER_BY_ASC) {
      $this->orderBy[] = array('column' => $column,
                               'order'  => $order);

      return $this;
    }

    public function mergeOrderByInto(QueryBuilder $QueryBuilder) {
      foreach ($this->orderBy as $currentOrderBy) {
        $QueryBuilder->orderBy($currentOrderBy['column'], $currentOrderBy['order']);
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

    public function limit($limit, $offset = 0) {
      $this->limit['limit'] = $limit;
      $this->limit['offset'] = $offset;

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
        $QueryBuilder->limit($this->getLimit(), $this->getLimitOffset());
      }

      return $QueryBuilder;
    }

    public function getQueryString($usePlaceholders = true) {
      $query = "";

      if (!empty($this->select)) {
        $query .= "SELECT " . $this->getSelectString();

        if (!empty($this->from)) {
          $query .= " FROM " . $this->getFromString();
        }

        if (!empty($this->where)) {
          $query .= " WHERE " . $this->getWhereString($usePlaceholders);
        }

        if (!empty($this->groupBy)) {
          $query .= " GROUP BY " . $this->getGroupByString();
        }

        if (!empty($this->having)) {
          $query .= " HAVING " . $this->getHavingString($usePlaceholders);
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

    public function getPlaceholderValues() {
      return array_merge($this->getWherePlaceholderValues(), $this->getHavingPlaceholderValues());
    }

    public function query() {
      $queryString = $this->getQueryString();

      if (!empty($queryString)) {
        $PdoStatement = $this->getPdoConnection()->prepare($queryString);
        $PdoStatement->execute($this->getPlaceholderValues());

        return $PdoStatement;
      }
      else {
        return false;
      }
    }

    public function __toString() {
      return $this->getQueryString(false);
    }

  }

?>