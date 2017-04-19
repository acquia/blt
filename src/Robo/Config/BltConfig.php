<?php

namespace Acquia\Blt\Robo\Config;

use Grasmash\YamlExpander\Expander;
use Robo\Config\Config;

/**
 *
 */
class BltConfig extends Config {

  /**
   * @param string $filename
   */
  public function expandFileProperties($filename) {
    $expanded_contents = Expander::expandArrayProperties(file($filename), $this->export());
    file_put_contents($filename, implode("", $expanded_contents));
  }

  /**
   * Set a config value.
   *
   * @param string $key
   * @param mixed $value
   *
   * @return $this
   */
  public function set($key, $value) {
    if ($value === 'false') {
      $value = FALSE;
    }
    elseif ($value === 'true') {
      $value = TRUE;
    }

    return parent::set($key, $value);

  }

}
