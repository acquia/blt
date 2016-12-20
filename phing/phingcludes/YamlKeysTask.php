<?php

use Symfony\Component\Yaml\Yaml;

/**
 * Class YamlVariableTask
 *
 * This task allows keys to be extracted from YAML variables and output to Phing
 * properties.
 */
class YamlKeysTask extends Task {

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
  protected $property = NULL;

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
    foreach ($parsed[$this->variable] as $key => $value) {
      $keys = array();
      if (!empty($this->property)) {
        $propChain = explode(self::PROP_DELIMITER, $this->property);
        $keys = $this->extractNestedKeys($value, $propChain);
      }
      else {
        $keys[] = $key;
      }

      if (empty($keys)) {
        throw new BuildException("Could not locate " .
          $this->variable . "[" . $key . "]" .
          (!empty($propChain) ? "[" . implode('][', $propChain) . "]" : '')
        );
      }

      $list = array_merge($list, $keys);
    }

    if (NULL !== $this->outputProperty) {
      $this->project->setProperty(
        $this->outputProperty,
        implode($this->listDelimiter, $list)
      );
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

  public function setProperty($property) {
    $this->property = $property;
  }

  public function setListDelimiter($listDelimiter) {
    $this->listDelimiter = $listDelimiter;
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

  private function extractNestedKeys(array $arr, array $propChain) {
    $values = array();
    $index = 0;
    $curArrPos = $arr;
    foreach ($propChain as $propKey) {
      if (array_key_exists($propKey, $curArrPos)) {
        $curArrPos = $curArrPos[$propKey];
        // If we're at the requested depth, extract
        // the keys here.
        if ($index === count($propChain) - 1) {
          foreach ($curArrPos as $key => $value) {
            $values[] = $key;
          }
        }
      }
      $index++;
    }

    return $values;
  }
}
