<?php

require_once 'phing/Task.php';

class RandomStringTask extends Task
{
  private $propertyName;

  /**
   * Set the name of the property to set.
   * @param string $v Property name
   * @return void
   */
  public function setPropertyName($v) {
    $this->propertyName = $v;
  }


  public function main() {
    if (!$this->propertyName) {
      throw new BuildException("You must specify the propertyName attribute", $this->getLocation());
    }

    $random = $this->string(55);
    $this->project->setProperty($this->propertyName, $random);
  }

  // @see https://api.drupal.org/api/drupal/core!lib!Drupal!Component!Utility!Random.php/function/Random%3A%3Astring/8.2.x
  public function string($length = 8, $unique = FALSE, $validator = NULL) {
    $counter = 0;

    // Continue to loop if $unique is TRUE and the generated string is not
    // unique or if $validator is a callable that returns FALSE. To generate a
    // random string this loop must be carried out at least once.
    do {
      if ($counter == static::MAXIMUM_TRIES) {
        throw new \RuntimeException('Unable to generate a unique random name');
      }
      $str = '';
      for ($i = 0; $i < $length; $i++) {
        $str .= chr(mt_rand(32, 126));
      }
      $counter++;

      $continue = FALSE;
      if ($unique) {
        $continue = isset($this->strings[$str]);
      }
      if (!$continue && is_callable($validator)) {
        // If the validator callback returns FALSE generate another random
        // string.
        $continue = !call_user_func($validator, $str);
      }
    } while ($continue);

    if ($unique) {
      $this->strings[$str] = TRUE;
    }

    return $str;
  }
}
