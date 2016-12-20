<?php

use Symfony\Component\Yaml\Yaml;

/**
 * Class YamlVariableTask
 *
 * This task allows YAML variables from YAML files to be output into Phing
 * properties.
 */
class YamlVariableTask extends Task {

  const PROP_DELIMITER = '.';

  /**
   * The YAML file from which to read the variable.
   * @var string
   */
  protected $file = NULL;


  /**
   * The YAML variable name. This may be a nested array.
   * @var string
   */
  protected $variable = NULL;

  /**
   * Nested property of $this->variable.
   * @var string
   */
  protected $variableProperty = NULL;

  protected $format = NULL;

  protected $listDelimiter = ',';

  /**
   * Property name to set with output value.
   * @var string
   */
  private $outputProperty;

  public function init() {
  }

  public function main() {

    $parsed = Yaml::parse(file_get_contents($this->file));

    $list = array();
    $propChain = $this->createPropertyChain($this->variableProperty);
    foreach ($parsed[$this->variable] as $key => $value) {
      if (!empty($this->variableProperty)) {
        $items = $this->extractNestedProperty($value, $propChain);
      }
      else {
        $items = $this->parseProperty($key, $value);
      }

      if (empty($items)) {
        throw new BuildException("Could not locate " .
        $this->variable . "[" . $key . "]" .
          (!empty($propChain) ? "[" . implode('][', $propChain) . "]" : '')
        );
      }

      $list = array_merge($list, $items);
    }

    $value = count($list) === 1 ? $list[0] : implode($this->listDelimiter, $list);


    if (NULL !== $this->outputProperty) {
      $this->project->setProperty($this->outputProperty, $value);
    }

  }

  /**
   * Sets $this->file property.
   *
   * @param string $file
   *   The PHP file from which to read the variable.
   */
  public function setFile($file) {
    $this->file = $file;
  }

  /**
   * Sets $this->variable
   *
   * @param string $variable
   *   The PHP variable name. This may be a nested array.
   */
  public function setVariable($variable) {
    $this->variable = $variable;
  }

  public function setVariableProperty($property) {
    $this->variableProperty = $property;
  }

  public function setListDelimiter($listDelimiter) {
    $this->listDelimiter = $listDelimiter;
  }

  /**
   * Sets $this->format property.
   *
   * @param string $format
   *   The user-defined output format for $this->outputProperty.
   */
  public function setFormat($format) {
    $this->format = $format;
  }

  /**
   * Sets $this->outputProperty property.
   *
   * @param string $prop
   *   The name of the Phing property whose value to set.
   */
  public function setOutputProperty($prop) {
    $this->outputProperty = $prop;
  }

  private function isAssociative(array $arr) {
    return !empty($arr) && array_keys($arr) !== range(0, count($arr) - 1);
  }

  private function parseProperty($key, $value){
    $values = array();
    if (is_array($value)) {
      // A property is being requested on an
      // associative array, return the property's
      // key.
      if ($this->isAssociative($value)) {
        $values[] = $key;
      }
      // The property can be reduced to a list of values,
      // simply return them.
      else {
        $values = $value;
      }
    }
    // This is a singular value.
    else{
      $values[] = $value;
    }

    return $values;
  }

  private function extractNestedProperty(array $arr, array $propChain) {
    $values = array();
    $index = 0;
    $curArrPos = $arr;
    foreach ($propChain as $key) {
      if (array_key_exists($key, $curArrPos)) {
        $curArrPos = $curArrPos[$key];
        // If we're at the requested depth.
        if ($index === count($propChain) - 1) {
          $values = $this->parseProperty($key, $curArrPos);
        }
      }
      $index++;
    }

    return $values;
  }

  private function createPropertyChain($propStr) {
    return explode(self::PROP_DELIMITER, $propStr);
  }
}
