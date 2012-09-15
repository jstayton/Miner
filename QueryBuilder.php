<?php

  /**
   * A dead simple PHP 5 OO interface for building SQL queries. No manual
   * string concatenation necessary.
   *
   * @author    Justin Stayton <justin.stayton@gmail.com>
   * @copyright Copyright 2011-2012 by Justin Stayton
   * @license   http://en.wikipedia.org/wiki/MIT_License MIT License
   * @package   QueryBuilder
   * @version   4.1.1
   */
  class QueryBuilder {

    /**
     * INNER JOIN type.
     */
    const INNER_JOIN = "INNER JOIN";

    /**
     * LEFT JOIN type.
     */
    const LEFT_JOIN = "LEFT JOIN";

    /**
     * RIGHT JOIN type.
     */
    const RIGHT_JOIN = "RIGHT JOIN";

    /**
     * AND logical operator.
     */
    const LOGICAL_AND = "AND";

    /**
     * OR logical operator.
     */
    const LOGICAL_OR = "OR";

    /**
     * Equals comparison operator.
     */
    const EQUALS = "=";

    /**
     * Not equals comparison operator.
     */
    const NOT_EQUALS = "!=";

    /**
     * Less than comparison operator.
     */
    const LESS_THAN = "<";

    /**
     * Less than or equal to comparison operator.
     */
    const LESS_THAN_OR_EQUAL = "<=";

    /**
     * Greater than comparison operator.
     */
    const GREATER_THAN = ">";

    /**
     * Greater than or equal to comparison operator.
     */
    const GREATER_THAN_OR_EQUAL = ">=";

    /**
     * IN comparison operator.
     */
    const IN = "IN";

    /**
     * NOT IN comparison operator.
     */
    const NOT_IN = "NOT IN";

    /**
     * LIKE comparison operator.
     */
    const LIKE = "LIKE";

    /**
     * NOT LIKE comparison operator.
     */
    const NOT_LIKE = "NOT LIKE";

    /**
     * ILIKE comparison operator.
     */
    const ILIKE = "ILIKE";

    /**
     * REGEXP comparison operator.
     */
    const REGEX = "REGEXP";

    /**
     * NOT REGEXP comparison operator.
     */
    const NOT_REGEX = "NOT REGEXP";

    /**
     * BETWEEN comparison operator.
     */
    const BETWEEN = "BETWEEN";

    /**
     * NOT BETWEEN comparison operator.
     */
    const NOT_BETWEEN = "NOT BETWEEN";

    /**
     * IS comparison operator.
     */
    const IS = "IS";

    /**
     * IS NOT comparison operator.
     */
    const IS_NOT = "IS NOT";

    /**
     * Ascending ORDER BY direction.
     */
    const ORDER_BY_ASC = "ASC";

    /**
     * Descending ORDER BY direction.
     */
    const ORDER_BY_DESC = "DESC";

    /**
     * Open bracket for grouping criteria.
     */
    const BRACKET_OPEN = "(";

    /**
     * Closing bracket for grouping criteria.
     */
    const BRACKET_CLOSE = ")";

    /**
     * PDO database connection to use in executing the query.
     *
     * @var PDO|null
     */
    private $PdoConnection;

    /**
     * Whether to automatically escape values.
     *
     * @var bool|null
     */
    private $autoQuote;

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
     * Table to INSERT into.
     *
     * @var string
     */
    private $insert;

    /**
     * Table to REPLACE into.
     *
     * @var string
     */
    private $replace;

    /**
     * Table to UPDATE.
     *
     * @var string
     */
    private $update;

    /**
     * Tables to DELETE from, or true if deleting from the FROM table.
     *
     * @var array|true
     */
    private $delete;

    /**
     * Column values to INSERT or UPDATE.
     *
     * @var array
     */
    private $set;

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
     * SET placeholder values.
     *
     * @var array
     */
    private $setPlaceholderValues;

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
     * @param  PDO|null $PdoConnection optional PDO database connection
     * @param  bool $autoQuote optional auto-escape values, default true
     * @return QueryBuilder
     */
    public function __construct(PDO $PdoConnection = null, $autoQuote = true) {
      $this->option = array();
      $this->select = array();
      $this->delete = array();
      $this->set = array();
      $this->from = array();
      $this->join = array();
      $this->where = array();
      $this->groupBy = array();
      $this->having = array();
      $this->orderBy = array();
      $this->limit = array();

      $this->wherePlaceholderValues = array();
      $this->havingPlaceholderValues = array();

      $this->setPdoConnection($PdoConnection)
           ->setAutoQuote($autoQuote);
    }

    /**
     * Set the PDO database connection to use in executing this query.
     *
     * @param  PDO|null $PdoConnection optional PDO database connection
     * @return QueryBuilder
     */
    public function setPdoConnection(PDO $PdoConnection = null) {
      $this->PdoConnection = $PdoConnection;

      return $this;
    }

    /**
     * Get the PDO database connection to use in executing this query.
     *
     * @return PDO|null
     */
    public function getPdoConnection() {
      return $this->PdoConnection;
    }

    /**
     * Set whether to automatically escape values.
     *
     * @param  bool|null $autoQuote whether to automatically escape values
     * @return QueryBuilder
     */
    public function setAutoQuote($autoQuote) {
      $this->autoQuote = $autoQuote;

      return $this;
    }

    /**
     * Get whether values will be automatically escaped.
     *
     * The $override parameter is for convenience in checking if a specific
     * value should be quoted differently than the rest. 'null' defers to the
     * global setting.
     *
     * @param  bool|null $override value-specific override for convenience
     * @return bool
     */
    public function getAutoQuote($override = null) {
      return $override === null ? $this->autoQuote : $override;
    }

    /**
     * Safely escape a value if auto-quoting is enabled, or do nothing if
     * disabled.
     *
     * The $override parameter is for convenience in checking if a specific
     * value should be quoted differently than the rest. 'null' defers to the
     * global setting.
     *
     * @param  mixed $value value to escape (or not)
     * @param  bool|null $override value-specific override for convenience
     * @return mixed|false value (escaped or original) or false if failed
     */
    public function autoQuote($value, $override = null) {
      return $this->getAutoQuote($override) ? $this->quote($value) : $value;
    }

    /**
     * Safely escape a value for use in a query.
     *
     * @param  mixed $value value to escape
     * @return mixed|false escaped value or false if failed
     */
    public function quote($value) {
      $PdoConnection = $this->getPdoConnection();

      // If a PDO database connection is set, use it to quote the value using
      // the underlying database. Otherwise, quote it manually.
      if ($PdoConnection) {
        return $PdoConnection->quote($value);
      }
      elseif (is_numeric($value)) {
        return $value;
      }
      else {
        return "'" . addslashes($value) . "'";
      }
    }

    /**
     * Add an execution option like DISTINCT or SQL_CALC_FOUND_ROWS.
     *
     * @param  string $option execution option to add
     * @return QueryBuilder
     */
    public function option($option) {
      $this->option[] = $option;

      return $this;
    }

    /**
     * Get the execution options portion of the query as a string.
     *
     * @param  bool $includeTrailingSpace optional include space after options
     * @return string execution options portion of the query
     */
    public function getOptionsString($includeTrailingSpace = false) {
      $query = "";

      if (!$this->option) {
        return $query;
      }

      $query .= implode(' ', $this->option);

      if ($includeTrailingSpace) {
        $query .= " ";
      }

      return $query;
    }

    /**
     * Merge this QueryBuilder's execution options into the given QueryBuilder.
     *
     * @param  QueryBuilder $QueryBuilder to merge into
     * @return QueryBuilder
     */
    public function mergeOptionsInto(QueryBuilder $QueryBuilder) {
      foreach ($this->option as $option) {
        $QueryBuilder->option($option);
      }

      return $QueryBuilder;
    }

    /**
     * Add SQL_CALC_FOUND_ROWS execution option.
     *
     * @return QueryBuilder
     */
    public function calcFoundRows() {
      return $this->option('SQL_CALC_FOUND_ROWS');
    }

    /**
     * Add DISTINCT execution option.
     *
     * @return QueryBuilder
     */
    public function distinct() {
      return $this->option('DISTINCT');
    }

    /**
     * Add a SELECT column, table, or expression with optional alias.
     *
     * @param  string $column column name, table name, or expression
     * @param  string $alias optional alias
     * @return QueryBuilder
     */
    public function select($column, $alias = null) {
      $this->select[$column] = $alias;

      return $this;
    }

    /**
     * Merge this QueryBuilder's SELECT into the given QueryBuilder.
     *
     * @param  QueryBuilder $QueryBuilder to merge into
     * @return QueryBuilder
     */
    public function mergeSelectInto(QueryBuilder $QueryBuilder) {
      $this->mergeOptionsInto($QueryBuilder);

      foreach ($this->select as $column => $alias) {
        $QueryBuilder->select($column, $alias);
      }

      return $QueryBuilder;
    }

    /**
     * Get the SELECT portion of the query as a string.
     *
     * @param  bool $includeText optional include 'SELECT' text, default true
     * @return string SELECT portion of the query
     */
    public function getSelectString($includeText = true) {
      $query = "";

      if (!$this->select) {
        return $query;
      }

      $query .= $this->getOptionsString(true);

      foreach ($this->select as $column => $alias) {
        $query .= $column;

        if ($alias) {
          $query .= " AS " . $alias;
        }

        $query .= ", ";
      }

      $query = substr($query, 0, -2);

      if ($includeText && $query) {
        $query = "SELECT " . $query;
      }

      return $query;
    }

    /**
     * Set the INSERT table.
     *
     * @param  string $table INSERT table
     * @return QueryBuilder
     */
    public function insert($table) {
      $this->insert = $table;

      return $this;
    }

    /**
     * Merge this QueryBuilder's INSERT into the given QueryBuilder.
     *
     * @param  QueryBuilder $QueryBuilder to merge into
     * @return QueryBuilder
     */
    public function mergeInsertInto(QueryBuilder $QueryBuilder) {
      $this->mergeOptionsInto($QueryBuilder);

      if ($this->insert) {
        $QueryBuilder->insert($this->getInsert());
      }

      return $QueryBuilder;
    }

    /**
     * Get the INSERT table.
     *
     * @return string INSERT table
     */
    public function getInsert() {
      return $this->insert;
    }

    /**
     * Get the INSERT portion of the query as a string.
     *
     * @param  bool $includeText optional include 'INSERT' text, default true
     * @return string INSERT portion of the query
     */
    public function getInsertString($includeText = true) {
      $query = "";

      if (!$this->insert) {
        return $query;
      }

      $query .= $this->getOptionsString(true);

      $query .= $this->getInsert();

      if ($includeText && $query) {
        $query = "INSERT " . $query;
      }

      return $query;
    }

    /**
     * Set the REPLACE table.
     *
     * @param  string $table REPLACE table
     * @return QueryBuilder
     */
    public function replace($table) {
      $this->replace = $table;

      return $this;
    }

    /**
     * Merge this QueryBuilder's REPLACE into the given QueryBuilder.
     *
     * @param  QueryBuilder $QueryBuilder to merge into
     * @return QueryBuilder
     */
    public function mergeReplaceInto(QueryBuilder $QueryBuilder) {
      $this->mergeOptionsInto($QueryBuilder);

      if ($this->replace) {
        $QueryBuilder->replace($this->getReplace());
      }

      return $QueryBuilder;
    }

    /**
     * Get the REPLACE table.
     *
     * @return string REPLACE table
     */
    public function getReplace() {
      return $this->replace;
    }

    /**
     * Get the REPLACE portion of the query as a string.
     *
     * @param  bool $includeText optional include 'REPLACE' text, default true
     * @return string REPLACE portion of the query
     */
    public function getReplaceString($includeText = true) {
      $query = "";

      if (!$this->replace) {
        return $query;
      }

      $query .= $this->getOptionsString(true);

      $query .= $this->getReplace();

      if ($includeText && $query) {
        $query = "REPLACE " . $query;
      }

      return $query;
    }

    /**
     * Set the UPDATE table.
     *
     * @param  string $table UPDATE table
     * @return QueryBuilder
     */
    public function update($table) {
      $this->update = $table;

      return $this;
    }

    /**
     * Merge this QueryBuilder's UPDATE into the given QueryBuilder.
     *
     * @param  QueryBuilder $QueryBuilder to merge into
     * @return QueryBuilder
     */
    public function mergeUpdateInto(QueryBuilder $QueryBuilder) {
      $this->mergeOptionsInto($QueryBuilder);

      if ($this->update) {
        $QueryBuilder->update($this->getUpdate());
      }

      return $QueryBuilder;
    }

    /**
     * Get the UPDATE table.
     *
     * @return string UPDATE table
     */
    public function getUpdate() {
      return $this->update;
    }

    /**
     * Get the UPDATE portion of the query as a string.
     *
     * @param  bool $includeText optional include 'UPDATE' text, default true
     * @return string UPDATE portion of the query
     */
    public function getUpdateString($includeText = true) {
      $query = "";

      if (!$this->update) {
        return $query;
      }

      $query .= $this->getOptionsString(true);

      $query .= $this->getUpdate();

      // Add any JOINs.
      $query .= " " . $this->getJoinString();

      $query  = rtrim($query);

      if ($includeText && $query) {
        $query = "UPDATE " . $query;
      }

      return $query;
    }

    /**
     * Add a table to DELETE from, or false if deleting from the FROM table.
     *
     * @param  string|false $table optional table name, default false
     * @return QueryBuilder
     */
    public function delete($table = false) {
      if ($table === false) {
        $this->delete = true;
      }
      else {
        // Reset the array in case the class variable was previously set to a
        // boolean value.
        if (!is_array($this->delete)) {
          $this->delete = array();
        }

        $this->delete[] = $table;
      }

      return $this;
    }

    /**
     * Merge this QueryBuilder's DELETE into the given QueryBuilder.
     *
     * @param  QueryBuilder $QueryBuilder to merge into
     * @return QueryBuilder
     */
    public function mergeDeleteInto(QueryBuilder $QueryBuilder) {
      $this->mergeOptionsInto($QueryBuilder);

      if ($this->isDeleteTableFrom()) {
        $QueryBuilder->delete();
      }
      else {
        foreach ($this->delete as $delete) {
          $QueryBuilder->delete($delete);
        }
      }

      return $QueryBuilder;
    }

    /**
     * Get the DELETE portion of the query as a string.
     *
     * @param  bool $includeText optional include 'DELETE' text, default true
     * @return string DELETE portion of the query
     */
    public function getDeleteString($includeText = true) {
      $query = "";

      if (!$this->delete && !$this->isDeleteTableFrom()) {
        return $query;
      }

      $query .= $this->getOptionsString(true);

      if (is_array($this->delete)) {
        $query .= implode(', ', $this->delete);
      }

      if ($includeText && ($query || $this->isDeleteTableFrom())) {
        $query = "DELETE " . $query;

        // Trim in case the table is specified in FROM.
        $query = trim($query);
      }

      return $query;
    }

    /**
     * Whether the FROM table is the single table to delete from.
     *
     * @return bool whether the delete table is FROM
     */
    private function isDeleteTableFrom() {
      return $this->delete === true;
    }

    /**
     * Add a column value to INSERT or UPDATE.
     *
     * @param  string $column column name
     * @param  mixed $value value
     * @param  bool|null $quote optional auto-escape value, default to global
     * @return QueryBuilder
     */
    public function set($column, $value, $quote = null) {
      $this->set[] = array('column' => $column,
                           'value'  => $value,
                           'quote'  => $quote);

      return $this;
    }

    /**
     * Merge this QueryBuilder's SET into the given QueryBuilder.
     *
     * @param  QueryBuilder $QueryBuilder to merge into
     * @return QueryBuilder
     */
    public function mergeSetInto(QueryBuilder $QueryBuilder) {
      foreach ($this->set as $set) {
        $QueryBuilder->set($set['column'], $set['value'], $set['quote']);
      }

      return $QueryBuilder;
    }

    /**
     * Get the SET portion of the query as a string.
     *
     * @param  bool $usePlaceholders optional use ? placeholders, default true
     * @param  bool $includeText optional include 'SET' text, default true
     * @return string SET portion of the query
     */
    public function getSetString($usePlaceholders = true, $includeText = true) {
      $query = "";
      $this->setPlaceholderValues = array();

      foreach ($this->set as $set) {
        $autoQuote = $this->getAutoQuote($set['quote']);

        if ($usePlaceholders && $autoQuote) {
          $query .= $set['column'] . " " . self::EQUALS . " ?, ";

          $this->setPlaceholderValues[] = $set['value'];
        }
        else {
          $query .= $set['column'] . " " . self::EQUALS . " " . $this->autoQuote($set['value'], $autoQuote) . ", ";
        }
      }

      $query = substr($query, 0, -2);

      if ($includeText && $query) {
        $query = "SET " . $query;
      }

      return $query;
    }

    /**
     * Get the SET placeholder values when {@link QueryBuilder::getSetString()}
     * is called with the parameter to use placeholder values.
     *
     * @return array SET placeholder values
     */
    public function getSetPlaceholderValues() {
      return $this->setPlaceholderValues;
    }

    /**
     * Set the FROM table with optional alias.
     *
     * @param  string $table table name
     * @param  string $alias optional alias
     * @return QueryBuilder
     */
    public function from($table, $alias = null) {
      $this->from['table'] = $table;
      $this->from['alias'] = $alias;

      return $this;
    }

    /**
     * Merge this QueryBuilder's FROM into the given QueryBuilder.
     *
     * @param  QueryBuilder $QueryBuilder to merge into
     * @return QueryBuilder
     */
    public function mergeFromInto(QueryBuilder $QueryBuilder) {
      if ($this->from) {
        $QueryBuilder->from($this->getFrom(), $this->getFromAlias());
      }

      return $QueryBuilder;
    }

    /**
     * Get the FROM table.
     *
     * @return string FROM table
     */
    public function getFrom() {
      return $this->from['table'];
    }

    /**
     * Get the FROM table alias.
     *
     * @return string FROM table alias
     */
    public function getFromAlias() {
      return $this->from['alias'];
    }

    /**
     * Add a JOIN table with optional ON criteria.
     *
     * @param  string $table table name
     * @param  string|array $criteria optional ON criteria
     * @param  string $type optional type of join, default INNER JOIN
     * @param  string $alias optional alias
     * @return QueryBuilder
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
     * Add an INNER JOIN table with optional ON criteria.
     *
     * @param  string $table table name
     * @param  string|array $criteria optional ON criteria
     * @param  string $alias optional alias
     * @return QueryBuilder
     */
    public function innerJoin($table, $criteria = null, $alias = null) {
      return $this->join($table, $criteria, self::INNER_JOIN, $alias);
    }

    /**
     * Add a LEFT JOIN table with optional ON criteria.
     *
     * @param  string $table table name
     * @param  string|array $criteria optional ON criteria
     * @param  string $alias optional alias
     * @return QueryBuilder
     */
    public function leftJoin($table, $criteria = null, $alias = null) {
      return $this->join($table, $criteria, self::LEFT_JOIN, $alias);
    }

    /**
     * Add a RIGHT JOIN table with optional ON criteria.
     *
     * @param  string $table table name
     * @param  string|array $criteria optional ON criteria
     * @param  string $alias optional alias
     * @return QueryBuilder
     */
    public function rightJoin($table, $criteria = null, $alias = null) {
      return $this->join($table, $criteria, self::RIGHT_JOIN, $alias);
    }

    /**
     * Merge this QueryBuilder's JOINs into the given QueryBuilder.
     *
     * @param  QueryBuilder $QueryBuilder to merge into
     * @return QueryBuilder
     */
    public function mergeJoinInto(QueryBuilder $QueryBuilder) {
      foreach ($this->join as $join) {
        $QueryBuilder->join($join['table'], $join['criteria'], $join['type'], $join['alias']);
      }

      return $QueryBuilder;
    }

    /**
     * Get an ON criteria string joining the specified table and column to the
     * same column of the previous JOIN or FROM table.
     *
     * @param  int $joinIndex index of current join
     * @param  string $table current table name
     * @param  string $column current column name
     * @return string ON join criteria
     */
    private function getJoinCriteriaUsingPreviousTable($joinIndex, $table, $column) {
      $joinCriteria = "";
      $previousJoinIndex = $joinIndex - 1;

      // If the previous table is from a JOIN, use that. Otherwise, use the
      // FROM table.
      if (array_key_exists($previousJoinIndex, $this->join)) {
        $previousTable = $this->join[$previousJoinIndex]['table'];
      }
      elseif ($this->isSelect()) {
        $previousTable = $this->getFrom();
      }
      elseif ($this->isUpdate()) {
        $previousTable = $this->getUpdate();
      }
      else {
        $previousTable = false;
      }

      // In the off chance there is no previous table.
      if ($previousTable) {
        $joinCriteria .= $previousTable . ".";
      }

      $joinCriteria .= $column . " " . self::EQUALS . " " . $table . "." . $column;

      return $joinCriteria;
    }

    /**
     * Get the JOIN portion of the query as a string.
     *
     * @return string JOIN portion of the query
     */
    public function getJoinString() {
      $query = "";

      foreach ($this->join as $i => $join) {
        $query .= " " . $join['type'] . " " . $join['table'];

        if ($join['alias']) {
          $query .= " AS " . $join['alias'];
        }

        // Add ON criteria if specified.
        if ($join['criteria']) {
          $query .= " ON ";

          foreach ($join['criteria'] as $x => $criterion) {
            // Logically join each criterion with AND.
            if ($x != 0) {
              $query .= " " . self::LOGICAL_AND . " ";
            }

            // If the criterion does not include an equals sign, assume a
            // column name and join against the same column from the previous
            // table.
            if (strpos($criterion, '=') === false) {
              $query .= $this->getJoinCriteriaUsingPreviousTable($i, $join['table'], $criterion);
            }
            else {
              $query .= $criterion;
            }
          }
        }
      }

      $query = trim($query);

      return $query;
    }

    /**
     * Get the FROM portion of the query, including all JOINs, as a string.
     *
     * @param  bool $includeText optional include 'FROM' text, default true
     * @return string FROM portion of the query
     */
    public function getFromString($includeText = true) {
      $query = "";

      if (!$this->from) {
        return $query;
      }

      $query .= $this->getFrom();

      if ($this->getFromAlias()) {
        $query .= " AS " . $this->getFromAlias();
      }

      // Add any JOINs.
      $query .= " " . $this->getJoinString();

      $query  = rtrim($query);

      if ($includeText && $query) {
        $query = "FROM " . $query;
      }

      return $query;
    }

    /**
     * Add an open bracket for nesting conditions to the specified WHERE or
     * HAVING criteria.
     *
     * @param  array $criteria WHERE or HAVING criteria
     * @param  string $connector optional logical connector, default AND
     * @return QueryBuilder
     */
    private function openCriteria(array &$criteria, $connector = self::LOGICAL_AND) {
      $criteria[] = array('bracket'   => self::BRACKET_OPEN,
                          'connector' => $connector);

      return $this;
    }

    /**
     * Add a closing bracket for nesting conditions to the specified WHERE or
     * HAVING criteria.
     *
     * @param  array $criteria WHERE or HAVING criteria
     * @return QueryBuilder
     */
    private function closeCriteria(array &$criteria) {
      $criteria[] = array('bracket'   => self::BRACKET_CLOSE,
                          'connector' => null);

      return $this;
    }

    /**
     * Add a condition to the specified WHERE or HAVING criteria.
     *
     * @param  array $criteria WHERE or HAVING criteria
     * @param  string $column column name
     * @param  mixed $value value
     * @param  string $operator optional comparison operator, default =
     * @param  string $connector optional logical connector, default AND
     * @param  bool|null $quote optional auto-escape value, default to global
     * @return QueryBuilder
     */
    private function criteria(array &$criteria, $column, $value, $operator = self::EQUALS,
                              $connector = self::LOGICAL_AND, $quote = null) {
      $criteria[] = array('column'    => $column,
                          'value'     => $value,
                          'operator'  => $operator,
                          'connector' => $connector,
                          'quote'     => $quote);

      return $this;
    }

    /**
     * Add an OR condition to the specified WHERE or HAVING criteria.
     *
     * @param  array $criteria WHERE or HAVING criteria
     * @param  string $column column name
     * @param  mixed $value value
     * @param  string $operator optional comparison operator, default =
     * @param  bool|null $quote optional auto-escape value, default to global
     * @return QueryBuilder
     */
    private function orCriteria(array &$criteria, $column, $value, $operator = self::EQUALS, $quote = null) {
      return $this->criteria($criteria, $column, $value, $operator, self::LOGICAL_OR, $quote);
    }

    /**
     * Add an IN condition to the specified WHERE or HAVING criteria.
     *
     * @param  array $criteria WHERE or HAVING criteria
     * @param  string $column column name
     * @param  array $values values
     * @param  string $connector optional logical connector, default AND
     * @param  bool|null $quote optional auto-escape value, default to global
     * @return QueryBuilder
     */
    private function criteriaIn(array &$criteria, $column, array $values, $connector = self::LOGICAL_AND,
                                $quote = null) {
      return $this->criteria($criteria, $column, $values, self::IN, $connector, $quote);
    }

    /**
     * Add a NOT IN condition to the specified WHERE or HAVING criteria.
     *
     * @param  array $criteria WHERE or HAVING criteria
     * @param  string $column column name
     * @param  array $values values
     * @param  string $connector optional logical connector, default AND
     * @param  bool|null $quote optional auto-escape value, default to global
     * @return QueryBuilder
     */
    private function criteriaNotIn(array &$criteria, $column, array $values, $connector = self::LOGICAL_AND,
                                   $quote = null) {
      return $this->criteria($criteria, $column, $values, self::NOT_IN, $connector, $quote);
    }

    /**
     * Add a BETWEEN condition to the specified WHERE or HAVING criteria.
     *
     * @param  array $criteria WHERE or HAVING criteria
     * @param  string $column column name
     * @param  mixed $min minimum value
     * @param  mixed $max maximum value
     * @param  string $connector optional logical connector, default AND
     * @param  bool|null $quote optional auto-escape value, default to global
     * @return QueryBuilder
     */
    private function criteriaBetween(array &$criteria, $column, $min, $max, $connector = self::LOGICAL_AND,
                                     $quote = null) {
      return $this->criteria($criteria, $column, array($min, $max), self::BETWEEN, $connector, $quote);
    }

    /**
     * Add a NOT BETWEEN condition to the specified WHERE or HAVING criteria.
     *
     * @param  array $criteria WHERE or HAVING criteria
     * @param  string $column column name
     * @param  mixed $min minimum value
     * @param  mixed $max maximum value
     * @param  string $connector optional logical connector, default AND
     * @param  bool|null $quote optional auto-escape value, default to global
     * @return QueryBuilder
     */
    private function criteriaNotBetween(array &$criteria, $column, $min, $max, $connector = self::LOGICAL_AND,
                                        $quote = null) {
      return $this->criteria($criteria, $column, array($min, $max), self::NOT_BETWEEN, $connector, $quote);
    }

    /**
     * Get the WHERE or HAVING portion of the query as a string.
     *
     * @param  array $criteria WHERE or HAVING criteria
     * @param  bool $usePlaceholders optional use ? placeholders, default true
     * @param  array $placeholderValues optional placeholder values array
     * @return string WHERE or HAVING portion of the query
     */
    private function getCriteriaString(array &$criteria, $usePlaceholders = true,
                                       array &$placeholderValues = array()) {
      $query = "";
      $placeholderValues = array();

      $useConnector = false;

      foreach ($criteria as $i => $criterion) {
        if (array_key_exists('bracket', $criterion)) {
          // If an open bracket, include the logical connector.
          if (strcmp($criterion['bracket'], self::BRACKET_OPEN) == 0) {
            if ($useConnector) {
              $query .= " " . $criterion['connector'] . " ";
            }

            $useConnector = false;
          }
          else {
            $useConnector = true;
          }

          $query .= $criterion['bracket'];
        }
        else {
          if ($useConnector) {
            $query .= " " . $criterion['connector'] . " ";
          }

          $useConnector = true;
          $autoQuote = $this->getAutoQuote($criterion['quote']);

          switch ($criterion['operator']) {
            case self::BETWEEN:
            case self::NOT_BETWEEN:
              if ($usePlaceholders && $autoQuote) {
                $value = "? " . self::LOGICAL_AND . " ?";

                $placeholderValues[] = $criterion['value'][0];
                $placeholderValues[] = $criterion['value'][1];
              }
              else {
                $value = $this->autoQuote($criterion['value'][0], $autoQuote) . " " . self::LOGICAL_AND . " " .
                         $this->autoQuote($criterion['value'][1], $autoQuote);
              }

              break;

            case self::IN:
            case self::NOT_IN:
              if ($usePlaceholders && $autoQuote) {
                $value = self::BRACKET_OPEN . substr(str_repeat('?, ', count($criterion['value'])), 0, -2) .
                         self::BRACKET_CLOSE;

                $placeholderValues = array_merge($placeholderValues, $criterion['value']);
              }
              else {
                $value = self::BRACKET_OPEN;

                foreach ($criterion['value'] as $criterionValue) {
                  $value .= $this->autoQuote($criterionValue, $autoQuote) . ", ";
                }

                $value  = substr($value, 0, -2);
                $value .= self::BRACKET_CLOSE;
              }

              break;

            case self::IS:
            case self::IS_NOT:
              $value = $criterion['value'];

              break;

            default:
              if ($usePlaceholders && $autoQuote) {
                $value = "?";

                $placeholderValues[] = $criterion['value'];
              }
              else {
                $value = $this->autoQuote($criterion['value'], $autoQuote);
              }

              break;
          }

          $query .= $criterion['column'] . " " . $criterion['operator'] . " " . $value;
        }
      }

      return $query;
    }

    /**
     * Add an open bracket for nesting WHERE conditions.
     *
     * @param  string $connector optional logical connector, default AND
     * @return QueryBuilder
     */
    public function openWhere($connector = self::LOGICAL_AND) {
      return $this->openCriteria($this->where, $connector);
    }

    /**
     * Add a closing bracket for nesting WHERE conditions.
     *
     * @return QueryBuilder
     */
    public function closeWhere() {
      return $this->closeCriteria($this->where);
    }

    /**
     * Add a WHERE condition.
     *
     * @param  string $column column name
     * @param  mixed $value value
     * @param  string $operator optional comparison operator, default =
     * @param  string $connector optional logical connector, default AND
     * @param  bool|null $quote optional auto-escape value, default to global
     * @return QueryBuilder
     */
    public function where($column, $value, $operator = self::EQUALS, $connector = self::LOGICAL_AND, $quote = null) {
      return $this->criteria($this->where, $column, $value, $operator, $connector, $quote);
    }

  	/**
     * Add an AND WHERE condition.
     *
     * @param  string $column colum name
     * @param  mixed $value value
     * @param  string $operator optional comparison operator, default =
     * @param  bool|null $quote optional auto-escape value, default to global
     * @return QueryBuilder
     */
    public function andWhere($column, $value, $operator = self::EQUALS, $quote = null) {
      return $this->criteria($this->where, $column, $value, $operator, self::LOGICAL_AND, $quote);
    }

    /**
     * Add an OR WHERE condition.
     *
     * @param  string $column colum name
     * @param  mixed $value value
     * @param  string $operator optional comparison operator, default =
     * @param  bool|null $quote optional auto-escape value, default to global
     * @return QueryBuilder
     */
    public function orWhere($column, $value, $operator = self::EQUALS, $quote = null) {
      return $this->orCriteria($this->where, $column, $value, $operator, self::LOGICAL_OR, $quote);
    }

    /**
     * Add an IN WHERE condition.
     *
     * @param  string $column column name
     * @param  array $values values
     * @param  string $connector optional logical connector, default AND
     * @param  bool|null $quote optional auto-escape value, default to global
     * @return QueryBuilder
     */
    public function whereIn($column, array $values, $connector = self::LOGICAL_AND, $quote = null) {
      return $this->criteriaIn($this->where, $column, $values, $connector, $quote);
    }

    /**
     * Add a NOT IN WHERE condition.
     *
     * @param  string $column column name
     * @param  array $values values
     * @param  string $connector optional logical connector, default AND
     * @param  bool|null $quote optional auto-escape value, default to global
     * @return QueryBuilder
     */
    public function whereNotIn($column, array $values, $connector = self::LOGICAL_AND, $quote = null) {
      return $this->criteriaNotIn($this->where, $column, $values, $connector, $quote);
    }

    /**
     * Add a BETWEEN WHERE condition.
     *
     * @param  string $column column name
     * @param  mixed $min minimum value
     * @param  mixed $max maximum value
     * @param  string $connector optional logical connector, default AND
     * @param  bool|null $quote optional auto-escape value, default to global
     * @return QueryBuilder
     */
    public function whereBetween($column, $min, $max, $connector = self::LOGICAL_AND, $quote = null) {
      return $this->criteriaBetween($this->where, $column, $min, $max, $connector, $quote);
    }

    /**
     * Add a NOT BETWEEN WHERE condition.
     *
     * @param  string $column column name
     * @param  mixed $min minimum value
     * @param  mixed $max maximum value
     * @param  string $connector optional logical connector, default AND
     * @param  bool|null $quote optional auto-escape value, default to global
     * @return QueryBuilder
     */
    public function whereNotBetween($column, $min, $max, $connector = self::LOGICAL_AND, $quote = null) {
      return $this->criteriaNotBetween($this->where, $column, $min, $max, $connector, $quote);
    }

    /**
     * Merge this QueryBuilder's WHERE into the given QueryBuilder.
     *
     * @param  QueryBuilder $QueryBuilder to merge into
     * @return QueryBuilder
     */
    public function mergeWhereInto(QueryBuilder $QueryBuilder) {
      foreach ($this->where as $where) {
        // Handle open/close brackets differently than other criteria.
        if (array_key_exists('bracket', $where)) {
          if (strcmp($where['bracket'], self::BRACKET_OPEN) == 0) {
            $QueryBuilder->openWhere($where['connector']);
          }
          else {
            $QueryBuilder->closeWhere();
          }
        }
        else {
          $QueryBuilder->where($where['column'], $where['value'], $where['operator'],
                               $where['connector'], $where['quote']);
        }
      }

      return $QueryBuilder;
    }

    /**
     * Get the WHERE portion of the query as a string.
     *
     * @param  bool $usePlaceholders optional use ? placeholders, default true
     * @param  bool $includeText optional include 'WHERE' text, default true
     * @return string WHERE portion of the query
     */
    public function getWhereString($usePlaceholders = true, $includeText = true) {
      $query = $this->getCriteriaString($this->where, $usePlaceholders, $this->wherePlaceholderValues);

      if ($includeText && $query) {
        $query = "WHERE " . $query;
      }

      return $query;
    }

    /**
     * Get the WHERE placeholder values when
     * {@link QueryBuilder::getWhereString()} is called with the parameter to
     * use placeholder values.
     *
     * @return array WHERE placeholder values
     */
    public function getWherePlaceholderValues() {
      return $this->wherePlaceholderValues;
    }

    /**
     * Add a GROUP BY column.
     *
     * @param  string $column column name
     * @param  string|null $order optional order direction, default none
     * @return QueryBuilder
     */
    public function groupBy($column, $order = null) {
      $this->groupBy[] = array('column' => $column,
                               'order'  => $order);

      return $this;
    }

    /**
     * Merge this QueryBuilder's GROUP BY into the given QueryBuilder.
     *
     * @param  QueryBuilder $QueryBuilder to merge into
     * @return QueryBuilder
     */
    public function mergeGroupByInto(QueryBuilder $QueryBuilder) {
      foreach ($this->groupBy as $groupBy) {
        $QueryBuilder->groupBy($groupBy['column'], $groupBy['order']);
      }

      return $QueryBuilder;
    }

    /**
     * Get the GROUP BY portion of the query as a string.
     *
     * @param  bool $includeText optional include 'GROUP BY' text, default true
     * @return string GROUP BY portion of the query
     */
    public function getGroupByString($includeText = true) {
      $query = "";

      foreach ($this->groupBy as $groupBy) {
        $query .= $groupBy['column'];

        if ($groupBy['order']) {
          $query .= " " . $groupBy['order'];
        }

        $query .= ", ";
      }

      $query = substr($query, 0, -2);

      if ($includeText && $query) {
        $query = "GROUP BY " . $query;
      }

      return $query;
    }

    /**
     * Add an open bracket for nesting HAVING conditions.
     *
     * @param  string $connector optional logical connector, default AND
     * @return QueryBuilder
     */
    public function openHaving($connector = self::LOGICAL_AND) {
      return $this->openCriteria($this->having, $connector);
    }

    /**
     * Add a closing bracket for nesting HAVING conditions.
     *
     * @return QueryBuilder
     */
    public function closeHaving() {
      return $this->closeCriteria($this->having);
    }

    /**
     * Add a HAVING condition.
     *
     * @param  string $column colum name
     * @param  mixed $value value
     * @param  string $operator optional comparison operator, default =
     * @param  string $connector optional logical connector, default AND
     * @param  bool|null $quote optional auto-escape value, default to global
     * @return QueryBuilder
     */
    public function having($column, $value, $operator = self::EQUALS, $connector = self::LOGICAL_AND, $quote = null) {
      return $this->criteria($this->having, $column, $value, $operator, $connector, $quote);
    }

  	/**
     * Add an AND HAVING condition.
     *
     * @param  string $column colum name
     * @param  mixed $value value
     * @param  string $operator optional comparison operator, default =
     * @param  bool|null $quote optional auto-escape value, default to global
     * @return QueryBuilder
     */
    public function andHaving($column, $value, $operator = self::EQUALS, $quote = null) {
      return $this->criteria($this->having, $column, $value, $operator, self::LOGICAL_AND, $quote);
    }

    /**
     * Add an OR HAVING condition.
     *
     * @param  string $column colum name
     * @param  mixed $value value
     * @param  string $operator optional comparison operator, default =
     * @param  bool|null $quote optional auto-escape value, default to global
     * @return QueryBuilder
     */
    public function orHaving($column, $value, $operator = self::EQUALS, $quote = null) {
      return $this->orCriteria($this->having, $column, $value, $operator, self::LOGICAL_OR, $quote);
    }

    /**
     * Add an IN WHERE condition.
     *
     * @param  string $column column name
     * @param  array $values values
     * @param  string $connector optional logical connector, default AND
     * @param  bool|null $quote optional auto-escape value, default to global
     * @return QueryBuilder
     */
    public function havingIn($column, array $values, $connector = self::LOGICAL_AND, $quote = null) {
      return $this->criteriaIn($this->having, $column, $values, $connector, $quote);
    }

    /**
     * Add a NOT IN HAVING condition.
     *
     * @param  string $column column name
     * @param  array $values values
     * @param  string $connector optional logical connector, default AND
     * @param  bool|null $quote optional auto-escape value, default to global
     * @return QueryBuilder
     */
    public function havingNotIn($column, array $values, $connector = self::LOGICAL_AND, $quote = null) {
      return $this->criteriaNotIn($this->having, $column, $values, $connector, $quote);
    }

    /**
     * Add a BETWEEN HAVING condition.
     *
     * @param  string $column column name
     * @param  mixed $min minimum value
     * @param  mixed $max maximum value
     * @param  string $connector optional logical connector, default AND
     * @param  bool|null $quote optional auto-escape value, default to global
     * @return QueryBuilder
     */
    public function havingBetween($column, $min, $max, $connector = self::LOGICAL_AND, $quote = null) {
      return $this->criteriaBetween($this->having, $column, $min, $max, $connector, $quote);
    }

    /**
     * Add a NOT BETWEEN HAVING condition.
     *
     * @param  string $column column name
     * @param  mixed $min minimum value
     * @param  mixed $max maximum value
     * @param  string $connector optional logical connector, default AND
     * @param  bool|null $quote optional auto-escape value, default to global
     * @return QueryBuilder
     */
    public function havingNotBetween($column, $min, $max, $connector = self::LOGICAL_AND, $quote = null) {
      return $this->criteriaNotBetween($this->having, $column, $min, $max, $connector, $quote);
    }

    /**
     * Merge this QueryBuilder's HAVING into the given QueryBuilder.
     *
     * @param  QueryBuilder $QueryBuilder to merge into
     * @return QueryBuilder
     */
    public function mergeHavingInto(QueryBuilder $QueryBuilder) {
      foreach ($this->having as $having) {
        // Handle open/close brackets differently than other criteria.
        if (array_key_exists('bracket', $having)) {
          if (strcmp($having['bracket'], self::BRACKET_OPEN) == 0) {
            $QueryBuilder->openHaving($having['connector']);
          }
          else {
            $QueryBuilder->closeHaving();
          }
        }
        else {
          $QueryBuilder->having($having['column'], $having['value'], $having['operator'],
                                $having['connector'], $having['quote']);
        }
      }

      return $QueryBuilder;
    }

    /**
     * Get the HAVING portion of the query as a string.
     *
     * @param  bool $usePlaceholders optional use ? placeholders, default true
     * @param  bool $includeText optional include 'HAVING' text, default true
     * @return string HAVING portion of the query
     */
    public function getHavingString($usePlaceholders = true, $includeText = true) {
      $query = $this->getCriteriaString($this->having, $usePlaceholders, $this->havingPlaceholderValues);

      if ($includeText && $query) {
        $query = "HAVING " . $query;
      }

      return $query;
    }

    /**
     * Get the HAVING placeholder values when
     * {@link QueryBuilder::getHavingString()} is called with the parameter to
     * use placeholder values.
     *
     * @return array HAVING placeholder values
     */
    public function getHavingPlaceholderValues() {
      return $this->havingPlaceholderValues;
    }

    /**
     * Add a column to ORDER BY.
     *
     * @param  string $column column name
     * @param  string $order optional order direction, default ASC
     * @return QueryBuilder
     */
    public function orderBy($column, $order = self::ORDER_BY_ASC) {
      $this->orderBy[] = array('column' => $column,
                               'order'  => $order);

      return $this;
    }

    /**
     * Merge this QueryBuilder's ORDER BY into the given QueryBuilder.
     *
     * @param  QueryBuilder $QueryBuilder to merge into
     * @return QueryBuilder
     */
    public function mergeOrderByInto(QueryBuilder $QueryBuilder) {
      foreach ($this->orderBy as $orderBy) {
        $QueryBuilder->orderBy($orderBy['column'], $orderBy['order']);
      }

      return $QueryBuilder;
    }

    /**
     * Get the ORDER BY portion of the query as a string.
     *
     * @param  bool $includeText optional include 'ORDER BY' text, default true
     * @return string ORDER BY portion of the query
     */
    public function getOrderByString($includeText = true) {
      $query = "";

      foreach ($this->orderBy as $orderBy) {
        $query .= $orderBy['column'] . " " . $orderBy['order'] . ", ";
      }

      $query = substr($query, 0, -2);

      if ($includeText && $query) {
        $query = "ORDER BY " . $query;
      }

      return $query;
    }

    /**
     * Set the LIMIT on number of rows to return with optional offset.
     *
     * @param  int|string $limit number of rows to return
     * @param  int|string $offset optional row number to start at, default 0
     * @return QueryBuilder
     */
    public function limit($limit, $offset = 0) {
      $this->limit['limit'] = $limit;
      $this->limit['offset'] = $offset;

      return $this;
    }

    /**
     * Merge this QueryBuilder's LIMIT into the given QueryBuilder.
     *
     * @param  QueryBuilder $QueryBuilder to merge into
     * @return QueryBuilder
     */
    public function mergeLimitInto(QueryBuilder $QueryBuilder) {
      if ($this->limit) {
        $QueryBuilder->limit($this->getLimit(), $this->getLimitOffset());
      }

      return $QueryBuilder;
    }

    /**
     * Get the LIMIT on number of rows to return.
     *
     * @return int|string LIMIT on number of rows to return
     */
    public function getLimit() {
      return $this->limit['limit'];
    }

    /**
     * Get the LIMIT row number to start at.
     *
     * @return int|string LIMIT row number to start at
     */
    public function getLimitOffset() {
      return $this->limit['offset'];
    }

    /**
     * Get the LIMIT portion of the query as a string.
     *
     * @param  bool $includeText optional include 'LIMIT' text, default true
     * @return string LIMIT portion of the query
     */
    public function getLimitString($includeText = true) {
      $query = "";

      if (!$this->limit) {
        return $query;
      }

      $query .= $this->limit['limit'];

      if ($this->limit['offset'] !== 0) {
        $query .= " OFFSET " . $this->limit['offset'];
      }

      if ($includeText && $query) {
        $query = "LIMIT " . $query;
      }

      return $query;
    }

    /**
     * Whether this is a SELECT query.
     *
     * @return bool whether this is a SELECT query
     */
    public function isSelect() {
      return !empty($this->select);
    }

    /**
     * Whether this is an INSERT query.
     *
     * @return bool whether this is an INSERT query
     */
    public function isInsert() {
      return !empty($this->insert);
    }

    /**
     * Whether this is a REPLACE query.
     *
     * @return bool whether this is a REPLACE query
     */
    public function isReplace() {
      return !empty($this->replace);
    }

    /**
     * Whether this is an UPDATE query.
     *
     * @return bool whether this is an UPDATE query
     */
    public function isUpdate() {
      return !empty($this->update);
    }

    /**
     * Whether this is a DELETE query.
     *
     * @return bool whether this is a DELETE query
     */
    public function isDelete() {
      return !empty($this->delete);
    }

    /**
     * Merge this QueryBuilder into the given QueryBuilder.
     *
     * @param  QueryBuilder $QueryBuilder to merge into
     * @param  bool $overwriteLimit optional overwrite limit, default true
     * @return QueryBuilder
     */
    public function mergeInto(QueryBuilder $QueryBuilder, $overwriteLimit = true) {
      if ($this->isSelect()) {
        $this->mergeSelectInto($QueryBuilder);
        $this->mergeFromInto($QueryBuilder);
        $this->mergeJoinInto($QueryBuilder);
        $this->mergeWhereInto($QueryBuilder);
        $this->mergeGroupByInto($QueryBuilder);
        $this->mergeHavingInto($QueryBuilder);
        $this->mergeOrderByInto($QueryBuilder);

        if ($overwriteLimit) {
          $this->mergeLimitInto($QueryBuilder);
        }
      }
      elseif ($this->isInsert()) {
        $this->mergeInsertInto($QueryBuilder);
        $this->mergeSetInto($QueryBuilder);
      }
      elseif ($this->isReplace()) {
        $this->mergeReplaceInto($QueryBuilder);
        $this->mergeSetInto($QueryBuilder);
      }
      elseif ($this->isUpdate()) {
        $this->mergeUpdateInto($QueryBuilder);
        $this->mergeJoinInto($QueryBuilder);
        $this->mergeSetInto($QueryBuilder);
        $this->mergeWhereInto($QueryBuilder);

        // ORDER BY and LIMIT are only applicable when updating a single table.
        if (!$this->join) {
          $this->mergeOrderByInto($QueryBuilder);

          if ($overwriteLimit) {
            $this->mergeLimitInto($QueryBuilder);
          }
        }
      }
      elseif ($this->isDelete()) {
        $this->mergeDeleteInto($QueryBuilder);
        $this->mergeFromInto($QueryBuilder);
        $this->mergeJoinInto($QueryBuilder);
        $this->mergeWhereInto($QueryBuilder);

        // ORDER BY and LIMIT are only applicable when deleting from a single
        // table.
        if ($this->isDeleteTableFrom()) {
          $this->mergeOrderByInto($QueryBuilder);

          if ($overwriteLimit) {
            $this->mergeLimitInto($QueryBuilder);
          }
        }
      }

      return $QueryBuilder;
    }

    /**
     * Get the full SELECT query string.
     *
     * @param  bool $usePlaceholders optional use ? placeholders, default true
     * @return string full SELECT query string
     */
    private function getSelectQueryString($usePlaceholders = true) {
      $query = "";

      if (!$this->isSelect()) {
        return $query;
      }

      $query .= $this->getSelectString();

      if ($this->from) {
        $query .= " " . $this->getFromString();
      }

      if ($this->where) {
        $query .= " " . $this->getWhereString($usePlaceholders);
      }

      if ($this->groupBy) {
        $query .= " " . $this->getGroupByString();
      }

      if ($this->having) {
        $query .= " " . $this->getHavingString($usePlaceholders);
      }

      if ($this->orderBy) {
        $query .= " " . $this->getOrderByString();
      }

      if ($this->limit) {
        $query .= " " . $this->getLimitString();
      }

      return $query;
    }

    /**
     * Get the full INSERT query string.
     *
     * @param  bool $usePlaceholders optional use ? placeholders, default true
     * @return string full INSERT query string
     */
    private function getInsertQueryString($usePlaceholders = true) {
      $query = "";

      if (!$this->isInsert()) {
        return $query;
      }

      $query .= $this->getInsertString();

      if ($this->set) {
        $query .= " " . $this->getSetString($usePlaceholders);
      }

      return $query;
    }

    /**
     * Get the full REPLACE query string.
     *
     * @param  bool $usePlaceholders optional use ? placeholders, default true
     * @return string full REPLACE query string
     */
    private function getReplaceQueryString($usePlaceholders = true) {
      $query = "";

      if (!$this->isReplace()) {
        return $query;
      }

      $query .= $this->getReplaceString();

      if ($this->set) {
        $query .= " " . $this->getSetString($usePlaceholders);
      }

      return $query;
    }

    /**
     * Get the full UPDATE query string.
     *
     * @param  bool $usePlaceholders optional use ? placeholders, default true
     * @return string full UPDATE query string
     */
    private function getUpdateQueryString($usePlaceholders = true) {
      $query = "";

      if (!$this->isUpdate()) {
        return $query;
      }

      $query .= $this->getUpdateString();

      if ($this->set) {
        $query .= " " . $this->getSetString($usePlaceholders);
      }

      if ($this->where) {
        $query .= " " . $this->getWhereString($usePlaceholders);
      }

      // ORDER BY and LIMIT are only applicable when updating a single table.
      if (!$this->join) {
        if ($this->orderBy) {
          $query .= " " . $this->getOrderByString();
        }

        if ($this->limit) {
          $query .= " " . $this->getLimitString();
        }
      }

      return $query;
    }

    /**
     * Get the full DELETE query string.
     *
     * @param  bool $usePlaceholders optional use ? placeholders, default true
     * @return string full DELETE query string
     */
    private function getDeleteQueryString($usePlaceholders = true) {
      $query = "";

      if (!$this->isDelete()) {
        return $query;
      }

      $query .= $this->getDeleteString();

      if ($this->from) {
        $query .= " " . $this->getFromString();
      }

      if ($this->where) {
        $query .= " " . $this->getWhereString($usePlaceholders);
      }

      // ORDER BY and LIMIT are only applicable when deleting from a single
      // table.
      if ($this->isDeleteTableFrom()) {
        if ($this->orderBy) {
          $query .= " " . $this->getOrderByString();
        }

        if ($this->limit) {
          $query .= " " . $this->getLimitString();
        }
      }

      return $query;
    }

    /**
     * Get the full query string.
     *
     * @param  bool $usePlaceholders optional use ? placeholders, default true
     * @return string full query string
     */
    public function getQueryString($usePlaceholders = true) {
      $query = "";

      if ($this->isSelect()) {
        $query = $this->getSelectQueryString($usePlaceholders);
      }
      elseif ($this->isInsert()) {
        $query = $this->getInsertQueryString($usePlaceholders);
      }
      elseif ($this->isReplace()) {
        $query = $this->getReplaceQueryString($usePlaceholders);
      }
      elseif ($this->isUpdate()) {
        $query = $this->getUpdateQueryString($usePlaceholders);
      }
      elseif ($this->isDelete()) {
        $query = $this->getDeleteQueryString($usePlaceholders);
      }

      return $query;
    }

    /**
     * Get all placeholder values when {@link QueryBuilder::getQueryString()}
     * is called with the parameter to use placeholder values.
     *
     * @return array all placeholder values
     */
    public function getPlaceholderValues() {
      return array_merge($this->getSetPlaceholderValues(),
                         $this->getWherePlaceholderValues(),
                         $this->getHavingPlaceholderValues());
    }

    /**
     * Execute the query using the PDO database connection.
     *
     * @return PDOStatement|false executed query or false if failed
     */
    public function query() {
      $PdoConnection = $this->getPdoConnection();

      // If no PDO database connection is set, the query cannot be executed.
      if (!$PdoConnection) {
        return false;
      }

      $queryString = $this->getQueryString();

      // Only execute if a query is set.
      if ($queryString) {
        $PdoStatement = $PdoConnection->prepare($queryString);
        $PdoStatement->execute($this->getPlaceholderValues());

        return $PdoStatement;
      }
      else {
        return false;
      }
    }

    /**
     * Get the full query string without value placeholders.
     *
     * @return string full query string
     */
    public function __toString() {
      return $this->getQueryString(false);
    }

  }

?>
