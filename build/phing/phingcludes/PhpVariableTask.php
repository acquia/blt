<?php

/**
 * Class PhpVariableTask
 *
 * This task allows php variables from php files to be output into Phing
 * properties.
 */
class PhpVariableTask extends Task {

  /**
   * The PHP file from which to read the variable.
   * @var string
   */
  protected $file = NULL;

  /**
   * The PHP variable name. This may be a nested array.
   * @var string
   */
  protected $variable = NULL;

  /**
   * Property name to set with output value.
   * @var string
   */
  private $outputProperty;

  public function init() {}

  public function main()
  {
    require $this->file;

    // @see http://php.net/manual/en/language.variables.variable.php
    $variable = $this->variable;

    // If variable is an array, parse array keys from string. Example value
    // would be databases[default][default][database].
    if (strstr($variable, '[')) {
      // Split string parts.
      $keys = preg_split('~(])?(\\[|$)~', $variable, -1, PREG_SPLIT_NO_EMPTY);

      // Determine the variable name. E.g., $database is variable in example.
      $variable_name = array_shift($keys);
      $value = $$variable_name;

      // Loop through nested array keys.
      foreach ($keys as $key)
      {
        if (!is_array($value) || !array_key_exists($key, $value)) {
          $value[$key] = NULL;
        }
        $value = &$value[$key];
      }
    }
    // If this is not an array, simply take variable value.
    else {
      $value = $$variable;
    }

    if (null !== $this->outputProperty) {
      $this->project->setProperty($this->outputProperty, $value);
    }

  }

  /**
   * Sets $this->file property.
   *
   * @param string $file
   *   The PHP file from which to read the variable.
   */
  public function setFile($file)
  {
    $this->file = $file;
  }

  /**
   * Sets $this->variable
   *
   * @param string $variable
   *   The PHP variable name. This may be a nested array.
   */
  public function setVariable($variable)
  {
    $this->variable = $variable;
  }

  /**
   * Sets $this->outputProperty property.
   *
   * @param string $prop
   *   The name of the Phing property whose value to set.
   */
  public function setOutputProperty($prop)
  {
    $this->outputProperty = $prop;
  }
}
