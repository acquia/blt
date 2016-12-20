<?php

use Symfony\Component\Yaml\Yaml;

/**
 * Class YamlKeysTask
 *
 * This task allows keys to be extracted from YAML variables and output to Phing
 * properties.
 */
class YamlKeysTask extends Task {


  /**
   * The property string delimiter.
   * @var string
   */
  const PROP_DELIMITER = '.';

  /**
   * The YAML file from which to read the keys.
   * @var string
   */
  protected $file = NULL;


  /**
   * The top-level YAML variable name.
   * @var string
   */
  protected $variable = NULL;

  /**
   * Nested property of $this->variable.
   * @var string
   */
  protected $property = NULL;

  /**
   * Output list delimiter.
   * @var string
   */
  protected $listDelimiter = ',';

  /**
   * Property name to set with output value.
   * @var string
   */
  private $outputProperty;

  public function init() {}

  public function main() {

    $parsed = Yaml::parse(file_get_contents($this->file));

    $list = array();
    foreach ($parsed[$this->variable] as $key => $value) {
      $keys = array();
      if (!empty($this->property)) {
        $keyChain = explode(self::PROP_DELIMITER, $this->property);
        $keys = $this->extractNestedKeys($value, $keyChain);
      }
      else {
        $keys[] = $key;
      }

      if (empty($keys)) {
        throw new BuildException("Could not locate " .
          $this->variable . "[" . $key . "]" .
          (!empty($keyChain) ? "[" . implode('][', $keyChain) . "]" : '')
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
   *   The YAML file from which to read the variable.
   */
  public function setFile($file) {
    $this->file = $file;
  }

  /**
   * Sets $this->variable
   *
   * @param string $variable
   *   The top-level YAML variable name.
   */
  public function setVariable($variable) {
    $this->variable = $variable;
  }

  /**
   * Sets $this->property
   *
   * @param $property string
   *  The nested property of $this->variable.
   */
  public function setProperty($property) {
    $this->property = $property;
  }

  /**
   * Sets $this->listDelimiter
   *
   * @param $listDelimiter string
   *    The character to use as a list item separator.
   */
  public function setListDelimiter($listDelimiter) {
    $this->listDelimiter = $listDelimiter;
  }

  /**
   * Sets $this->outputProperty property.
   *
   * @param $prop string
   *   The name of the Phing property whose value to set.
   */
  public function setOutputProperty($prop) {
    $this->outputProperty = $prop;
  }

  /**
   * Returns the keys nested within an array under particular sequence of keys.
   *
   * @param array $arr
   *  An array which contains a nested series of keys.
   * @param $keyChain array
   *  An array of keys to traverse.
   * @return array
   *  The extracted keys from $arr
   */
  private function extractNestedKeys(array $arr, array $keyChain) {
    $values = array();
    $index = 0;
    $curArrPos = $arr;
    foreach ($keyChain as $arrKey) {
      if (array_key_exists($arrKey, $curArrPos)) {
        $curArrPos = $curArrPos[$arrKey];
        // If we're at the requested depth, extract
        // the keys here.
        if ($index === count($keyChain) - 1) {
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
