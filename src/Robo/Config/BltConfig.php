<?php

namespace Acquia\Blt\Robo\Config;

use Acquia\Blt\Robo\Common\ArrayManipulator;
use Dflydev\DotAccessData\Data;
use Grasmash\YamlExpander\Expander;
use Robo\Config\Config;
use Symfony\Component\Yaml\Yaml;

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
   * Set a config value
   *
   * @param string $key
   * @param mixed $value
   *
   * @return $this
   */
  public function set($key, $value)
  {
    if ($value === 'false') {
      $value = false;
    }
    elseif ($value === 'true') {
      $value = true;
    }

    return parent::set($key, $value);

  }

}
