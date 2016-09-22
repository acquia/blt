<?php

/**
 * A phing task to loop through properties.
 */
require_once 'phing/Task.php';

use Symfony\Component\Yaml\Yaml;

/**
 * Parse a given yml file to locate targets for a specific key.
 *
 * @author Steve Worley <sj.worley88@gmail.com>
 */
class TargetsTask extends PropertyTask {

  /**
   * Path to a file that contains targets.
   *
   * @var string
   */
  protected $file;

  /**
   * Key by which to find targets.
   *
   * @var string
   */
  protected $key;

  /**
   * Return property name.
   *
   * @var string
   */
  protected $property;

  /**
   * Delimteter to join return values by.
   *
   * @var string
   */
  protected $glue;

  /**
   * Default targets to return if $key is not found in $file.
   *
   * Should match the expected input for Phing <foreach>.
   *
   * @example
   *    validate:all,ci:setup,tests:all
   *
   * @see https://www.phing.info/docs/guide/trunk/ForeachTask.html
   *
   * @var string
   */
  protected $default;

  /**
   * Set the file path.
   *
   * @param string $file
   *   A path to a valid yml file.
   */
  public function setFile($file) {
    $this->file = $file;
  }

  /**
   * Set the key to lookup.
   *
   * @param string $key
   *   A key to look for in $file.
   */
  public function setKey($key) {
    $this->key = $key;
  }

  /**
   * Set the return property name.
   *
   * @param string $property
   *   A name that will be accessible by other phing tasks in the target.
   */
  public function setProperty($property = 'targets') {
    $this->property = $property;
  }

  /**
   * Set the glue for the return value.
   *
   * This will be used by implode; if passing to phing target <foreach> the
   * value given here should be set in foreach as well.
   *
   * @see https://www.phing.info/docs/guide/trunk/ForeachTask.html
   *
   * @param string $glue
   *   A string to join the return value.
   */
  public function setGlue($glue = ',') {
    $this->glue = $glue;
  }

  /**
   * Default list of targets to call.
   *
   * Should match the expected input for Phing <foreach>.
   *
   * @example
   *    validate:all,ci:setup,tests:all
   *
   * @see https://www.phing.info/docs/guide/trunk/ForeachTask.html
   *
   * @param string $default
   *   A glue separated string.
   */
  public function setDefault($default = "") {
    $this->default = $default;
  }

  /**
   * Getter for file property.
   *
   * @return string
   */
  public function getFile() {
    return $this->file;
  }

  /**
   * Getter for key property.
   *
   * @return string
   */
  public function getKey() {
    return $this->key;
  }

  /**
   * Getter for property property.
   *
   * @return string
   */
  public function getProperty() {
    return $this->property;
  }

  /**
   * Getter for glue property.
   *
   * @return string
   */
  public function getGlue() {
    return $this->glue;
  }

  /**
   * Getter for default property.
   *
   * @return string
   */
  public function getDefault() {
    return $this->default;
  }

  /**
   * The main entry point method.
   *
   * @return bool
   */
  public function main() {

    $file = $this->loadFile($this->getFile());
    $targets = $file['targets'];

    if (empty($targets) || empty($targets[$this->getKey()])) {
      $this->log("No targets defined for {$this->getKey()}");
      $this->project->setProperty($this->getProperty(), $this->getDefault());
      return FALSE;
    }

    $values = $targets[$this->getKey()];
    $parsed_target_list = [];

    foreach ($this->arrayFlatten($values) as $name => $value) {
      array_push($parsed_target_list, "$name:" . substr($value, 0, -2));
    }

    $parsed_target_list = implode($this->getGlue(), $parsed_target_list);
    $this->project->setProperty($this->getProperty(), $parsed_target_list);
    return TRUE;
  }

  /**
   * Load the Yaml file given.
   *
   * @param string $file
   *   A file path.
   *
   * @return array
   *   An associative array of parsed YAML.
   *
   * @throws BuildException
   */
  public function loadFile($file) {
    try {
      if (file_exists($file)) {
        $data = Yaml::parse(file_get_contents($file));
      }
    } catch (Exception $e) {
      throw new BuildException('Could not load given file.');
    }

    return $data;
  }

  /**
   * Flatten array values into a string.
   *
   * @param array $array
   *   A list of values.
   *
   * @return array
   */
  public function arrayFlatten($array) {
    $result = array();
    $stack = array();
    array_push($stack, array("", $array));

    while (count($stack) > 0) {
      list($prefix, $array) = array_pop($stack);

      foreach ($array as $key => $value) {
        $new_key = $prefix . strval($key);

        if (is_array($value)) {
          array_push($stack, array($new_key . ':', $value));
        } else {
          $result[$new_key] = $value;
        }
      }
    }

    return $result;
  }

}
