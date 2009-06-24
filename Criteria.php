<?php

  class Criteria {

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

    const LOGICAL_AND = "AND";
    const LOGICAL_OR = "OR";

    private $criteria;

    public function __construct() {
      $this->criteria = array();
    }

    public function add($column, $value, $operator = self::EQUALS, $connector = self::LOGICAL_AND) {
      $this->criteria[] = array('column'    => $column,
                                'value'     => $value,
                                'operator'  => $operator,
                                'connector' => $connector);

      return $this;
    }

    public function addIn($column, array $values, $notIn = false, $connector = self::LOGICAL_AND) {
      if ($notIn) {
        $operator = self::NOT_IN;
      }
      else {
        $operator = self::IN;
      }

      $value = "(" . implode(', ', $values) . ")";

      $this->add($column, $value, $operator, $connector);

      return $this;
    }

    public function addBetween($column, $min, $max, $notBetween = false, $connector = self::LOGICAL_AND) {
      if ($notBetween) {
        $operator = self::NOT_BETWEEN;
      }
      else {
        $operator = self::BETWEEN;
      }

      $value = $min . " AND " . $max;

      $this->add($column, $value, $operator, $connector);

      return $this;
    }

    public function addCriteria(Criteria $Criteria, $connector = self::LOGICAL_AND) {
      $this->criteria[] = array('Criteria'  => $Criteria,
                                'connector' => $connector);

      return $this;
    }

    public function getCriteria() {
      $criteria = "";

      foreach ($this->criteria as $i => $currentCriterion) {
        if ($i != 0) {
          $criteria .= " " . $currentCriterion['connector'] . " ";
        }

        if (array_key_exists('Criteria', $currentCriterion)) {
          $criteria .= "(" . $currentCriterion['Criteria']->toString() . ")";
        }
        else {
          $criteria .= $currentCriterion['column'] . " " . $currentCriterion['operator'] . " " .
                       $currentCriterion['value'];
        }
      }

      return $criteria;
    }

  }

?>