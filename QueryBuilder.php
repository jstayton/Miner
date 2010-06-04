<?php

  /**
   * Programmatically build MySQL SELECT queries without the overhead of
   * passing strings between functions. QueryBuilders can also be merged
   * together for easy query modification, with an optional PDO database
   * connection to directly execute the query.
   *
   * @author   Justin Stayton <justin.stayton@gmail.com>
   * @version  3.0
   */
  class QueryBuilderQueryBuilder {

    /**
     * JOIN types.
     */
    const INNER_JOIN = "INNER JOIN";
    const LEFT_JOIN = "LEFT JOIN";
    const RIGHT_JOIN = "RIGHT JOIN";

    /**
     * Logical operators.
     */
    const LOGICAL_AND = "AND";
    const LOGICAL_OR = "OR";

    /**
     * Comparison operators.
     */
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

    /**
     * ORDER BY directions.
     */
    const ORDER_BY_ASC = "ASC";
    const ORDER_BY_DESC = "DESC";

    /**
     * Brackets for grouping criteria.
     */
    const BRACKET_OPEN = "(";
    const BRACKET_CLOSE = ")";

    /**
     * PDO database connection to use in executing the query.
     *
     * @var PDO
     */
    private $PdoConnection;

    /**
     * Execution options like DISTINCT and SQL_CALC_FOUND_ROWS.
     *
     * @var array
     */
    private $option;

    /**
     * Columns, tables, and expressions to SELECT from.
     *
     * @var array
     */
    private $select;

    /**
     * Table to select FROM.
     *
     * @var array
     */
    private $from;

    /**
     * JOIN tables and ON criteria.
     *
     * @var array
     */
    private $join;

    /**
     * WHERE criteria.
     *
     * @var array
     */
    private $where;

    /**
     * Columns to GROUP BY.
     *
     * @var array
     */
    private $groupBy;

    /**
     * HAVING criteria.
     *
     * @var array
     */
    private $having;

    /**
     * Columns to ORDER BY.
     *
     * @var array
     */
    private $orderBy;

    /**
     * Number of rows to return from offset.
     *
     * @var array
     */
    private $limit;

    /**
     * WHERE placeholder values.
     *
     * @var array
     */
    private $wherePlaceholderValues;

    /**
     * HAVING placeholder values.
     *
     * @var array
     */
    private $havingPlaceholderValues;

    /**
     * Constructor.
     *
     * @param  PDO $PdoConnection optional PDO database connection
     * @return QueryBuilder
     * @uses   QueryBuilder::setPdoConnection()
     * @uses   QueryBuilder::$from
     * @uses   QueryBuilder::$groupBy
     * @uses   QueryBuilder::$having
     * @uses   QueryBuilder::$havingPlaceholderValues
     * @uses   QueryBuilder::$join
     * @uses   QueryBuilder::$limit
     * @uses   QueryBuilder::$option
     * @uses   QueryBuilder::$orderBy
     * @uses   QueryBuilder::$select
     * @uses   QueryBuilder::$where
     * @uses   QueryBuilder::$wherePlaceholderValues
     */
    public function __construct(PDO $PdoConnection = null) {
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

    /**
     * Sets the PDO database connection to use in executing this query.
     *
     * @param  PDO|null $PdoConnection optional PDO database connection
     * @return QueryBuilder
     * @uses   QueryBuilder::$PdoConnection
     */
    public function setPdoConnection(PDO $PdoConnection = null) {
      $this->PdoConnection = $PdoConnection;

      return $this;
    }

    /**
     * Returns the PDO database connection to use in executing this query.
     *
     * @return PDO|null
     * @uses   QueryBuilder::$PdoConnection
     */
    public function getPdoConnection() {
      return $this->PdoConnection;
    }

    /**
     * Safely escapes a value for use in a query.
     *
     * @param  string $value value to escape
     * @return string|false
     * @uses   QueryBuilder::getPdoConnection()
     */
    public function quote($value) {
      $PdoConnection = $this->getPdoConnection();

      // If a PDO database connection is set, use it to quote the value using
      // the underlying database. Otherwise, quote it manually.
      if (isset($PdoConnection)) {
        return $PdoConnection->quote($value);
      }
      else {
        if (is_numeric($value)) {
          return $value;
        }
        else {
          return "'" . addslashes($value) . "'";
        }
      }
    }

    /**
     * Adds an execution option like DISTINCT or SQL_CALC_FOUND_ROWS.
     *
     * @param  string $option execution option to add
     * @return QueryBuilder
     * @uses   QueryBuilder::$option
     */
    public function option($option) {
      $this->option[] = $option;

      return $this;
    }

    /**
     * Adds SQL_CALC_FOUND_ROWS execution option.
     *
     * @return QueryBuilder
     * @uses   QueryBuilder::option()
     */
    public function calcFoundRows() {
      return $this->option('SQL_CALC_FOUND_ROWS');
    }

    /**
     * Adds DISTINCT execution option.
     *
     * @return QueryBuilder
     * @uses   QueryBuilder::option()
     */
    public function distinct() {
      return $this->option('DISTINCT');
    }

    /**
     * Adds a SELECT column, table, or expression with optional alias.
     *
     * @param  string $column column name, table name, or expression
     * @param  string $alias optional alias
     * @return QueryBuilder
     * @uses   QueryBuilder::$select
     */
    public function select($column, $alias = null) {
      $this->select[$column] = $alias;

      return $this;
    }

    /**
     * Merges this QueryBuilder's SELECT into the given QueryBuilder.
     *
     * @param  QueryBuilder $QueryBuilder to merge into
     * @return QueryBuilder
     * @uses   QueryBuilder::$option
     * @uses   QueryBuilder::$select
     * @uses   QueryBuilder::option()
     * @uses   QueryBuilder::select()
     */
    public function mergeSelectInto(QueryBuilder $QueryBuilder) {
      foreach ($this->option as $currentOption) {
        $QueryBuilder->option($currentOption);
      }

      foreach ($this->select as $currentColumn => $currentAlias) {
        $QueryBuilder->select($currentColumn, $currentAlias);
      }

      return $QueryBuilder;
    }

    /**
     * Returns the SELECT portion of the query as a string.
     *
     * @return string
     * @uses   QueryBuilder::$option
     * @uses   QueryBuilder::$select
     */
    public function getSelectString() {
      $select = "";

      // Add any execution options.
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

    /**
     * Sets the FROM table with optional alias.
     *
     * @param  string $table table name
     * @param  string $alias optional alias
     * @return QueryBuilder
     * @uses   QueryBuilder::$from
     */
    public function from($table, $alias = null) {
      $this->from['table'] = $table;
      $this->from['alias'] = $alias;

      return $this;
    }

    /**
     * Returns the FROM table.
     *
     * @return string
     * @uses   QueryBuilder::$from
     */
    public function getFrom() {
      return $this->from['table'];
    }

    /**
     * Returns the FROM table alias.
     *
     * @return string
     * @uses   QueryBuilder::$from
     */
    public function getFromAlias() {
      return $this->from['alias'];
    }

    /**
     * Adds a JOIN table with optional ON criteria.
     *
     * @param  string $table table name
     * @param  string|array $criteria optional ON criteria
     * @param  string $type optional type of join, default INNER JOIN
     * @param  string $alias optional alias
     * @return QueryBuilder
     * @uses   QueryBuilder::INNER_JOIN
     * @uses   QueryBuilder::$join
     */
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

    /**
     * Adds an INNER JOIN table with optional ON criteria.
     *
     * @param  string $table table name
     * @param  string|array $criteria optional ON criteria
     * @param  string $alias optional alias
     * @return QueryBuilder
     * @uses   QueryBuilder::INNER_JOIN
     * @uses   QueryBuilder::join()
     */
    public function innerJoin($table, $criteria = null, $alias = null) {
      return $this->join($table, $criteria, self::INNER_JOIN, $alias);
    }

    /**
     * Adds a LEFT JOIN table with optional ON criteria.
     *
     * @param  string $table table name
     * @param  string|array $criteria optional ON criteria
     * @param  string $alias optional alias
     * @return QueryBuilder
     * @uses   QueryBuilder::LEFT_JOIN
     * @uses   QueryBuilder::join()
     */
    public function leftJoin($table, $criteria = null, $alias = null) {
      return $this->join($table, $criteria, self::LEFT_JOIN, $alias);
    }

    /**
     * Adds a RIGHT JOIN table with optional ON criteria.
     *
     * @param  string $table table name
     * @param  string|array $criteria optional ON criteria
     * @param  string $alias optional alias
     * @return QueryBuilder
     * @uses   QueryBuilder::RIGHT_JOIN
     * @uses   QueryBuilder::join()
     */
    public function rightJoin($table, $criteria = null, $alias = null) {
      return $this->join($table, $criteria, self::RIGHT_JOIN, $alias);
    }

    /**
     * Merges this QueryBuilder's JOINs into the given QueryBuilder.
     *
     * @param  string QueryBuilder $QueryBuilder to merge into
     * @return QueryBuilder
     * @uses   QueryBuilder::$join
     * @uses   QueryBuilder::join()
     */
    public function mergeJoinInto(QueryBuilder $QueryBuilder) {
      foreach ($this->join as $currentJoin) {
        $QueryBuilder->join($currentJoin['table'], $currentJoin['criteria'], $currentJoin['type'],
                            $currentJoin['alias']);
      }

      return $QueryBuilder;
    }

    /**
     * Returns an ON criteria string joining the specified table and column to
     * the same column of the previous JOIN or FROM table.
     *
     * @param  int $joinIndex index of current join
     * @param  string $table current table name
     * @param  string $column current column name
     * @return string
     * @uses   QueryBuilder::$join
     * @uses   QueryBuilder::getFrom()
     */
    private function getJoinCriteriaUsingPreviousTable($joinIndex, $table, $column) {
      $previousJoinIndex = $joinIndex - 1;

      // If the previous table is from a JOIN, use that. Otherwise, use the
      // FROM table.
      if (array_key_exists($previousJoinIndex, $this->join)) {
        $previousTable = $this->join[$previousJoinIndex]['table'];
      }
      else {
        $previousTable = $this->getFrom();
      }

      return $previousTable . "." . $column . " = " . $table . "." . $column;
    }

    /**
     * Returns the JOIN portion of the query as a string.
     *
     * @return string
     * @uses   QueryBuilder::$join
     * @uses   QueryBuilder::LOGICAL_AND
     * @uses   QueryBuilder::getJoinCriteriaUsingPreviousTable()
     */
    public function getJoinString() {
      $join = "";

      foreach ($this->join as $i => $currentJoin) {
        $join .= " " . $currentJoin['type'] . " " . $currentJoin['table'];

        if (isset($currentJoin['alias'])) {
          $join .= " AS " . $currentJoin['alias'];
        }

        // Add ON criteria if specified.
        if (isset($currentJoin['criteria'])) {
          $join .= " ON ";

          foreach ($currentJoin['criteria'] as $x => $criterion) {
            // Logically join each criterion with AND.
            if ($x != 0) {
              $join .= " " . self::LOGICAL_AND . " ";
            }

            // If the criterion does not include an equals sign, assume a
            // column name and join against the same column from the previous
            // table.
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

    /**
     * Returns the FROM portion of the query, including all JOINs, as a string.
     *
     * @return string
     * @uses   QueryBuilder::$from
     * @uses   QueryBuilder::getJoinString()
     */
    public function getFromString() {
      $from = "";

      if (!empty($this->from)) {
        $from .= $this->from['table'];

        if (isset($this->from['alias'])) {
          $from .= " AS " . $this->from['alias'];
        }

        // Add any JOINs.
        $from .= " " . $this->getJoinString();
      }

      $from = rtrim($from);

      return $from;
    }

    /**
     * Adds an open bracket for nesting conditions to the specified WHERE or
     * HAVING criteria.
     *
     * @param  array $criteria WHERE or HAVING criteria
     * @param  string $connector optional logical connector, default AND
     * @return QueryBuilder
     * @uses   QueryBuilder::LOGICAL_AND
     * @uses   QueryBuilder::BRACKET_OPEN
     */
    private function openCriteria(array &$criteria, $connector = self::LOGICAL_AND) {
      $criteria[] = array('bracket'   => self::BRACKET_OPEN,
                          'connector' => $connector);

      return $this;
    }

    /**
     * Adds a closing bracket for nesting conditions to the specified WHERE or
     * HAVING criteria.
     *
     * @param  array $criteria WHERE or HAVING criteria
     * @return QueryBuilder
     * @uses   QueryBuilder::BRACKET_CLOSE
     */
    private function closeCriteria(array &$criteria) {
      $criteria[] = array('bracket'   => self::BRACKET_CLOSE,
                          'connector' => null);

      return $this;
    }

    /**
     * Adds a condition to the specified WHERE or HAVING criteria.
     *
     * @param  array $criteria WHERE or HAVING criteria
     * @param  string $column column name
     * @param  mixed $value value
     * @param  string $operator optional comparison operator, default =
     * @param  string $connector optional logical connector, default AND
     * @return QueryBuilder
     * @uses   QueryBuilder::EQUALS
     * @uses   QueryBuilder::LOGICAL_AND
     */
    private function criteria(array &$criteria, $column, $value, $operator = self::EQUALS, $connector = self::LOGICAL_AND) {
      $criteria[] = array('column'    => $column,
                          'value'     => $value,
                          'operator'  => $operator,
                          'connector' => $connector);

      return $this;
    }

    /**
     * Adds an OR condition to the specified WHERE or HAVING criteria.
     *
     * @param  array $criteria WHERE or HAVING criteria
     * @param  string $column column name
     * @param  mixed $value value
     * @param  string $operator optional comparison operator, default =
     * @return QueryBuilder
     * @uses   QueryBuilder::EQUALS
     * @uses   QueryBuilder::LOGICAL_OR
     * @uses   QueryBuilder::criteria()
     */
    private function orCriteria(array &$criteria, $column, $value, $operator = self::EQUALS) {
      return $this->criteria($criteria, $column, $value, $operator, self::LOGICAL_OR);
    }

    /**
     * Adds an IN condition to the specified WHERE or HAVING criteria.
     *
     * @param  array $criteria WHERE or HAVING criteria
     * @param  string $column column name
     * @param  array $values values
     * @param  string $connector optional logical connector, default AND
     * @return QueryBuilder
     * @uses   QueryBuilder::LOGICAL_AND
     * @uses   QueryBuilder::IN
     * @uses   QueryBuilder::criteria()
     */
    private function criteriaIn(array &$criteria, $column, array $values, $connector = self::LOGICAL_AND) {
      return $this->criteria($criteria, $column, $values, self::IN, $connector);
    }

    /**
     * Adds a NOT IN condition to the specified WHERE or HAVING criteria.
     *
     * @param  array $criteria WHERE or HAVING criteria
     * @param  string $column column name
     * @param  array $values values
     * @param  string $connector optional logical connector, default AND
     * @return QueryBuilder
     * @uses   QueryBuilder::LOGICAL_AND
     * @uses   QueryBuilder::NOT_IN
     * @uses   QueryBuilder::criteria()
     */
    private function criteriaNotIn(array &$criteria, $column, array $values, $connector = self::LOGICAL_AND) {
      return $this->criteria($criteria, $column, $values, self::NOT_IN, $connector);
    }

    /**
     * Adds a BETWEEN condition to the specified WHERE or HAVING criteria.
     *
     * @param  array $criteria WHERE or HAVING criteria
     * @param  string $column column name
     * @param  mixed $min minimum value
     * @param  mixed $max maximum value
     * @param  string $connector optional logical connector, default AND
     * @return QueryBuilder
     * @uses   QueryBuilder::LOGICAL_AND
     * @uses   QueryBuilder::BETWEEN
     * @uses   QueryBuilder::criteria()
     */
    private function criteriaBetween(array &$criteria, $column, $min, $max, $connector = self::LOGICAL_AND) {
      return $this->criteria($criteria, $column, array($min, $max), self::BETWEEN, $connector);
    }

    /**
     * Adds a NOT BETWEEN condition to the specified WHERE or HAVING criteria.
     *
     * @param  array $criteria WHERE or HAVING criteria
     * @param  string $column column name
     * @param  mixed $min minimum value
     * @param  mixed $max maximum value
     * @param  string $connector optional logical connector, default AND
     * @return QueryBuilder
     * @uses   QueryBuilder::LOGICAL_AND
     * @uses   QueryBuilder::NOT_BETWEEN
     * @uses   QueryBuilder::criteria()
     */
    private function criteriaNotBetween(array &$criteria, $column, $min, $max, $connector = self::LOGICAL_AND) {
      return $this->criteria($criteria, $column, array($min, $max), self::NOT_BETWEEN, $connector);
    }

    /**
     * Returns the WHERE or HAVING portion of the query as a string.
     *
     * @param  array $criteria WHERE or HAVING criteria
     * @param  bool $usePlaceholders optional use ? placeholders, default true
     * @param  array $placeholderValues optional placeholder values array
     * @return string
     * @uses   QueryBuilder::BRACKET_OPEN
     * @uses   QueryBuilder::BRACKET_CLOSE
     * @uses   QueryBuilder::BETWEEN
     * @uses   QueryBuilder::NOT_BETWEEN
     * @uses   QueryBuilder::IN
     * @uses   QueryBuilder::NOT_IN
     * @uses   QueryBuilder::IS
     * @uses   QueryBuilder::IS_NOT
     * @uses   QueryBuilder::LOGICAL_AND
     * @uses   QueryBuilder::quote()
     */
    private function getCriteriaString(array &$criteria, $usePlaceholders = true, array &$placeholderValues = array()) {
      $string = "";

      $useConnector = false;

      foreach ($criteria as $i => $currentCriterion) {
        if (array_key_exists('bracket', $currentCriterion)) {
          // If an open bracket, include the logical connector.
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
                $value = "? " . self::LOGICAL_AND . " ?";

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

    /**
     * Adds an open bracket for nesting WHERE conditions.
     *
     * @param  string $connector optional logical connector, default AND
     * @return QueryBuilder
     * @uses   QueryBuilder::LOGICAL_AND
     * @uses   QueryBuilder::$where
     * @uses   QueryBuilder::openCriteria()
     */
    public function openWhere($connector = self::LOGICAL_AND) {
      return $this->openCriteria($this->where, $connector);
    }

    /**
     * Adds a closing bracket for nesting WHERE conditions.
     *
     * @return QueryBuilder
     * @uses   QueryBuilder::$where
     * @uses   QueryBuilder::closeCriteria()
     */
    public function closeWhere() {
      return $this->closeCriteria($this->where);
    }

    /**
     * Adds a WHERE condition.
     *
     * @param  string $column column name
     * @param  mixed $value value
     * @param  string $operator optional comparison operator, default =
     * @param  string $connector optional logical connector, default AND
     * @return QueryBuilder
     * @uses   QueryBuilder::EQUALS
     * @uses   QueryBuilder::LOGICAL_AND
     * @uses   QueryBuilder::$where
     * @uses   QueryBuilder::criteria()
     */
    public function where($column, $value, $operator = self::EQUALS, $connector = self::LOGICAL_AND) {
      return $this->criteria($this->where, $column, $value, $operator, $connector);
    }

    /**
     * Adds an OR WHERE condition.
     *
     * @param  string $column colum name
     * @param  mixed $value value
     * @param  string $operator optional comparison operator, default =
     * @return QueryBuilder
     * @uses   QueryBuilder::EQUALS
     * @uses   QueryBuilder::LOGICAL_OR
     * @uses   QueryBuilder::$where
     * @uses   QueryBuilder::orCriteria()
     */
    public function orWhere($column, $value, $operator = self::EQUALS) {
      return $this->orCriteria($this->where, $column, $value, $operator, self::LOGICAL_OR);
    }

    /**
     * Adds an IN WHERE condition.
     *
     * @param  string $column column name
     * @param  array $values values
     * @param  string $connector optional logical connector, default AND
     * @return QueryBuilder
     * @uses   QueryBuilder::LOGICAL_AND
     * @uses   QueryBuilder::$where
     * @uses   QueryBuilder::criteriaIn()
     */
    public function whereIn($column, array $values, $connector = self::LOGICAL_AND) {
      return $this->criteriaIn($this->where, $column, $values, $connector);
    }

    /**
     * Adds a NOT IN WHERE condition.
     *
     * @param  string $column column name
     * @param  array $values values
     * @param  string $connector optional logical connector, default AND
     * @return QueryBuilder
     * @uses   QueryBuilder::LOGICAL_AND
     * @uses   QueryBuilder::$where
     * @uses   QueryBuilder::criteriaNotIn()
     */
    public function whereNotIn($column, array $values, $connector = self::LOGICAL_AND) {
      return $this->criteriaNotIn($this->where, $column, $values, $connector);
    }

    /**
     * Adds a BETWEEN WHERE condition.
     *
     * @param  string $column column name
     * @param  mixed $min minimum value
     * @param  mixed $max maximum value
     * @param  string $connector optional logical connector, default AND
     * @return QueryBuilder
     * @uses   QueryBuilder::LOGICAL_AND
     * @uses   QueryBuilder::$where
     * @uses   QueryBuilder::criteriaBetween()
     */
    public function whereBetween($column, $min, $max, $connector = self::LOGICAL_AND) {
      return $this->criteriaBetween($this->where, $column, $min, $max, $connector);
    }

    /**
     * Adds a NOT BETWEEN WHERE condition.
     *
     * @param  string $column column name
     * @param  mixed $min minimum value
     * @param  mixed $max maximum value
     * @param  string $connector optional logical connector, default AND
     * @return QueryBuilder
     * @uses   QueryBuilder::LOGICAL_AND
     * @uses   QueryBuilder::$where
     * @uses   QueryBuilder::criteriaNotBetween()
     */
    public function whereNotBetween($column, $min, $max, $connector = self::LOGICAL_AND) {
      return $this->criteriaNotBetween($this->where, $column, $min, $max, $connector);
    }

    /**
     * Merges this QueryBuilder's WHERE into the given QueryBuilder.
     *
     * @param  QueryBuilder $QueryBuilder to merge into
     * @return QueryBuilder
     * @uses   QueryBuilder::BRACKET_OPEN
     * @uses   QueryBuilder::$where
     * @uses   QueryBuilder::openWhere()
     * @uses   QueryBuilder::closeWhere()
     * @uses   QueryBuilder::where()
     */
    public function mergeWhereInto(QueryBuilder $QueryBuilder) {
      foreach ($this->where as $currentWhere) {
        // Handle open/close brackets differently than other criteria.
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

    /**
     * Returns the WHERE portion of the query as a string.
     *
     * @param  bool $usePlaceholders optional use ? placeholders, default true
     * @return string
     * @uses   QueryBuilder::$where
     * @uses   QueryBuilder::$wherePlaceholderValues
     * @uses   QueryBuilder::getCriteriaString()
     */
    public function getWhereString($usePlaceholders = true) {
      return $this->getCriteriaString($this->where, $usePlaceholders, $this->wherePlaceholderValues);
    }

    /**
     * Returns the WHERE placeholder values when
     * {@link QueryBuilder::getWhereString()} is called with the parameter to
     * use placeholder values.
     *
     * @return array
     * @uses   QueryBuilder::$wherePlaceholderValues
     */
    public function getWherePlaceholderValues() {
      return $this->wherePlaceholderValues;
    }

    /**
     * Adds a GROUP BY column.
     *
     * @param  string $column column name
     * @param  string $order optional order direction, default ASC
     * @return QueryBuilder
     * @uses   QueryBuilder::ORDER_BY_ASC
     * @uses   QueryBuilder::$groupBy
     */
    public function groupBy($column, $order = self::ORDER_BY_ASC) {
      $this->groupBy[] = array('column' => $column,
                               'order'  => $order);

      return $this;
    }

    /**
     * Merges this QueryBuilder's GROUP BY into the given QueryBuilder.
     *
     * @param  QueryBuilder $QueryBuilder to merge into
     * @return QueryBuilder
     * @uses   QueryBuilder::$groupBy
     * @uses   QueryBuilder::groupBy()
     */
    public function mergeGroupByInto(QueryBuilder $QueryBuilder) {
      foreach ($this->groupBy as $currentGroupBy) {
        $QueryBuilder->groupBy($currentGroupBy['column'], $currentGroupBy['order']);
      }

      return $QueryBuilder;
    }

    /**
     * Returns the GROUP BY portion of the query as a string.
     *
     * @return string
     * @uses   QueryBuilder::$groupBy
     */
    public function getGroupByString() {
      $groupBy = "";

      foreach ($this->groupBy as $currentGroupBy) {
        $groupBy .= $currentGroupBy['column'] . " " . $currentGroupBy['order'] . ", ";
      }

      $groupBy = substr($groupBy, 0, -2);

      return $groupBy;
    }

    /**
     * Adds an open bracket for nesting HAVING conditions.
     *
     * @param  string $connector optional logical connector, default AND
     * @return QueryBuilder
     * @uses   QueryBuilder::LOGICAL_AND
     * @uses   QueryBuilder::$having
     * @uses   QueryBuilder::openCriteria()
     */
    public function openHaving($connector = self::LOGICAL_AND) {
      return $this->openCriteria($this->having, $connector);
    }

    /**
     * Adds a closing bracket for nesting HAVING conditions.
     *
     * @return QueryBuilder
     * @uses   QueryBuilder::$having
     * @uses   QueryBuilder::closeCriteria()
     */
    public function closeHaving() {
      return $this->closeCriteria($this->having);
    }

    /**
     * Adds a HAVING condition.
     *
     * @param  string $column colum name
     * @param  mixed $value value
     * @param  string $operator optional comparison operator, default =
     * @param  string $connector optional logical connector, default AND
     * @return QueryBuilder
     * @uses   QueryBuilder::EQUALS
     * @uses   QueryBuilder::LOGICAL_AND
     * @uses   QueryBuilder::$having
     * @uses   QueryBuilder::criteria()
     */
    public function having($column, $value, $operator = self::EQUALS, $connector = self::LOGICAL_AND) {
      return $this->criteria($this->having, $column, $value, $operator, $connector);
    }

    /**
     * Adds an OR HAVING condition.
     *
     * @param  string $column colum name
     * @param  mixed $value value
     * @param  string $operator optional comparison operator, default =
     * @return QueryBuilder
     * @uses   QueryBuilder::EQUALS
     * @uses   QueryBuilder::LOGICAL_OR
     * @uses   QueryBuilder::$having
     * @uses   QueryBuilder::orCriteria()
     */
    public function orHaving($column, $value, $operator = self::EQUALS) {
      return $this->orCriteria($this->having, $column, $value, $operator, self::LOGICAL_OR);
    }

    /**
     * Adds an IN WHERE condition.
     *
     * @param  string $column column name
     * @param  array $values values
     * @param  string $connector optional logical connector, default AND
     * @return QueryBuilder
     * @uses   QueryBuilder::LOGICAL_AND
     * @uses   QueryBuilder::$having
     * @uses   QueryBuilder::criteriaIn()
     */
    public function havingIn($column, array $values, $connector = self::LOGICAL_AND) {
      return $this->criteriaIn($this->having, $column, $values, $connector);
    }

    /**
     * Adds a NOT IN HAVING condition.
     *
     * @param  string $column column name
     * @param  array $values values
     * @param  string $connector optional logical connector, default AND
     * @return QueryBuilder
     * @uses   QueryBuilder::LOGICAL_AND
     * @uses   QueryBuilder::$having
     * @uses   QueryBuilder::criteriaNotIn()
     */
    public function havingNotIn($column, array $values, $connector = self::LOGICAL_AND) {
      return $this->criteriaNotIn($this->having, $column, $values, $connector);
    }

    /**
     * Adds a BETWEEN HAVING condition.
     *
     * @param  string $column column name
     * @param  mixed $min minimum value
     * @param  mixed $max maximum value
     * @param  string $connector optional logical connector, default AND
     * @return QueryBuilder
     * @uses   QueryBuilder::LOGICAL_AND
     * @uses   QueryBuilder::$having
     * @uses   QueryBuilder::criteriaBetween()
     */
    public function havingBetween($column, $min, $max, $connector = self::LOGICAL_AND) {
      return $this->criteriaBetween($this->having, $column, $min, $max, $connector);
    }

    /**
     * Adds a NOT BETWEEN HAVING condition.
     *
     * @param  string $column column name
     * @param  mixed $min minimum value
     * @param  mixed $max maximum value
     * @param  string $connector optional logical connector, default AND
     * @return QueryBuilder
     * @uses   QueryBuilder::LOGICAL_AND
     * @uses   QueryBuilder::$having
     * @uses   QueryBuilder::criteriaNotBetween()
     */
    public function havingNotBetween($column, $min, $max, $connector = self::LOGICAL_AND) {
      return $this->criteriaNotBetween($this->having, $column, $min, $max, $connector);
    }

    /**
     * Merges this QueryBuilder's HAVING into the given QueryBuilder.
     *
     * @param  QueryBuilder $QueryBuilder to merge into
     * @return QueryBuilder
     * @uses   QueryBuilder::BRACKET_OPEN
     * @uses   QueryBuilder::$having
     * @uses   QueryBuilder::openHaving()
     * @uses   QueryBuilder::closeHaving()
     * @uses   QueryBuilder::having()
     */
    public function mergeHavingInto(QueryBuilder $QueryBuilder) {
      foreach ($this->having as $currentHaving) {
        // Handle open/close brackets differently than other criteria.
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

    /**
     * Returns the HAVING portion of the query as a string.
     *
     * @param  bool $usePlaceholders optional use ? placeholders, default true
     * @return string
     * @uses   QueryBuilder::$having
     * @uses   QueryBuilder::$havingPlaceholderValues
     * @uses   QueryBuilder::getCriteriaString()
     */
    public function getHavingString($usePlaceholders = true) {
      return $this->getCriteriaString($this->having, $usePlaceholders, $this->havingPlaceholderValues);
    }

    /**
     * Returns the HAVING placeholder values when
     * {@link QueryBuilder::getHavingString()} is called with the parameter to
     * use placeholder values.
     *
     * @return array
     * @uses   QueryBuilder::$havingPlaceholderValues
     */
    public function getHavingPlaceholderValues() {
      return $this->havingPlaceholderValues;
    }

    /**
     * Adds a column to ORDER BY.
     *
     * @param  string $column column name
     * @param  string $order optional order direction, default ASC
     * @return QueryBuilder
     * @uses   QueryBuilder::ORDER_BY_ASC
     * @uses   QueryBuilder::$orderBy
     */
    public function orderBy($column, $order = self::ORDER_BY_ASC) {
      $this->orderBy[] = array('column' => $column,
                               'order'  => $order);

      return $this;
    }

    /**
     * Merges this QueryBuilder's ORDER BY into the given QueryBuilder.
     *
     * @param  QueryBuilder $QueryBuilder to merge into
     * @return QueryBuilder
     * @uses   QueryBuilder::$orderBy
     * @uses   QueryBuilder::orderBy()
     */
    public function mergeOrderByInto(QueryBuilder $QueryBuilder) {
      foreach ($this->orderBy as $currentOrderBy) {
        $QueryBuilder->orderBy($currentOrderBy['column'], $currentOrderBy['order']);
      }

      return $QueryBuilder;
    }

    /**
     * Returns the ORDER BY portion of the query as a string.
     *
     * @return string
     * @uses   QueryBuilder::$orderBy
     */
    public function getOrderByString() {
      $orderBy = "";

      foreach ($this->orderBy as $currentOrderBy) {
        $orderBy .= $currentOrderBy['column'] . " " . $currentOrderBy['order'] . ", ";
      }

      $orderBy = substr($orderBy, 0, -2);

      return $orderBy;
    }

    /**
     * Sets the LIMIT on number of rows to return with optional offset.
     *
     * @param  int|string $limit number of rows to return
     * @param  int|string $offset optional row number to start at, default 0
     * @return QueryBuilder
     * @uses   QueryBuilder::$limit
     */
    public function limit($limit, $offset = 0) {
      $this->limit['limit'] = $limit;
      $this->limit['offset'] = $offset;

      return $this;
    }

    /**
     * Returns the LIMIT on number of rows to return.
     *
     * @return int|string
     * @uses   QueryBuilder::$limit
     */
    public function getLimit() {
      return $this->limit['limit'];
    }

    /**
     * Returns the LIMIT row number to start at.
     *
     * @return int|string
     * @uses   QueryBuilder::$limit
     */
    public function getLimitOffset() {
      return $this->limit['offset'];
    }

    /**
     * Returns the LIMIT portion of the query as a string.
     *
     * @return string
     * @uses   QueryBuilder::$limit
     */
    public function getLimitString() {
      $limit = "";

      if (!empty($this->limit)) {
        $limit .= $this->limit['offset'] . ", " . $this->limit['limit'];
      }

      return $limit;
    }

    /**
     * Merges this QueryBuilder into the given QueryBuilder.
     *
     * @param  QueryBuilder $QueryBuilder to merge into
     * @param  bool $overwriteLimit optional overwrite limit, default true
     * @return QueryBuilder
     * @uses   QueryBuilder::mergeSelectInto()
     * @uses   QueryBuilder::mergeJoinInto()
     * @uses   QueryBuilder::mergeWhereInto()
     * @uses   QueryBuilder::mergeGroupByInto()
     * @uses   QueryBuilder::mergeHavingInto()
     * @uses   QueryBuilder::mergeOrderByInto()
     * @uses   QueryBuilder::$limit
     * @uses   QueryBuilder::limit()
     * @uses   QueryBuilder::getLimit()
     * @uses   QueryBuilder::getLimitOffset()
     */
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

    /**
     * Returns the full query string.
     *
     * @param  bool $usePlaceholders optional use ? placeholders, default true
     * @return string
     * @uses   QueryBuilder::$select
     * @uses   QueryBuilder::$from
     * @uses   QueryBuilder::$where
     * @uses   QueryBuilder::$groupBy
     * @uses   QueryBuilder::$having
     * @uses   QueryBuilder::$orderBy
     * @uses   QueryBuilder::$limit
     * @uses   QueryBuilder::getSelectString()
     * @uses   QueryBuilder::getFromString()
     * @uses   QueryBuilder::getWhereString()
     * @uses   QueryBuilder::getGroupByString()
     * @uses   QueryBuilder::getHavingString()
     * @uses   QueryBuilder::getOrderByString()
     * @uses   QueryBuilder::getLimitString()
     */
    public function getQueryString($usePlaceholders = true) {
      $query = "";

      // Only return the full query string if a SELECT value is set.
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

    /**
     * Returns all placeholder values when
     * {@link QueryBuilder::getQueryString()} is called with the parameter to
     * use placeholder values.
     *
     * @return array
     * @uses   QueryBuilder::getWherePlaceholderValues()
     * @uses   QueryBuilder::getHavingPlaceholderValues()
     */
    public function getPlaceholderValues() {
      return array_merge($this->getWherePlaceholderValues(), $this->getHavingPlaceholderValues());
    }

    /**
     * Executes the query using the PDO database connection.
     *
     * @return PDOStatement|false
     * @uses   QueryBuilder::getQueryString()
     * @uses   QueryBuilder::getPdoConnection()
     * @uses   QueryBuilder::getPlaceholderValues()
     */
    public function query() {
      $PdoConnection = $this->getPdoConnection();

      // If no PDO database connection is set, the query cannot be executed.
      if (!isset($PdoConnection)) {
        return false;
      }

      $queryString = $this->getQueryString();

      // Only execute if a query is set.
      if (!empty($queryString)) {
        $PdoStatement = $PdoConnection->prepare($queryString);
        $PdoStatement->execute($this->getPlaceholderValues());

        return $PdoStatement;
      }
      else {
        return false;
      }
    }

    /**
     * Returns the full query string without value placeholders.
     *
     * @return string
     * @uses   QueryBuilder::getQueryString()
     */
    public function __toString() {
      return $this->getQueryString(false);
    }

  }

?>