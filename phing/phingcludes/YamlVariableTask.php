<?php

use Symfony\Component\Yaml\Yaml;

/**
 * Class YamlVariableTask
 *
 * This task allows YAML variables from YAML files to be output into Phing
 * properties.
 */
class YamlVariableTask extends Task {

  const FORMAT_LIST = 'list';
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

  protected $dummy = NULL;

  /**
   * Property name to set with output value.
   * @var string
   */
  private $outputProperty;

  public function init() {
  }

  public function main() {

    $parsed = Yaml::parse(file_get_contents($this->file));

    switch ($this->format) {
      case self::FORMAT_LIST:
        $list = array();
        foreach ($parsed[$this->variable] as $itemKey => $item) {
          $nested = NULL;
          $current = &$item;
          if (!empty($this->variableProperty)) {
            $propChain = $this->createPropertyChain($this->variableProperty);
            $nested = $this->extractNestedProperty($current, $propChain);
            // @todo unified error handling.
            if ($nested === FALSE) {
              throw new BuildException(
                "Could not locate " .
                $this->variable . "[" . $itemKey . "]" .
                "[" . implode('][', $propChain) . "]"
              );
            }
          }

          if (empty($nested)) {
            $append = $this->isAssociative($item) ? $itemKey : $item;
          }
          else {
            $append = $nested;
          }

          $list[] = is_array($append) ? implode($this->listDelimiter, $append) : $append;

        }

        $value = implode($this->listDelimiter, $list);
        break;

      default:
        // @todo unified error handling.
        $value = $parsed[$this->variable];
        if($this->variableProperty){
          $value = $this->extractNestedProperty(
            $value, $this->createPropertyChain($this->variableProperty)
          );
        }
    }

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
   * @remove
   */
  public function setDummy($dummy) {
    $this->dummy = $dummy;
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

  private function extractNestedProperty(array $arr, array $propChain) {
    $index = 0;
    $current = $arr;
    foreach ($propChain as $key) {
      if (array_key_exists($key, $current)) {
        $current = $current[$key];
        // If we're at the requested depth.
        if ($index === count($propChain) - 1) {
          return is_array($current) && $this->isAssociative($current)
            ? $key : $current;

        }
        $index++;
        continue;
      }
      // Not a valid key.
      break;

    }

    return FALSE;
  }

  private function createPropertyChain($propStr){
    return explode(self::PROP_DELIMITER, $propStr);
  }
}
