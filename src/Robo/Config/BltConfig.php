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
  static function expandFileProperties($filename) {
    $expanded_config = Expander::parse(file_get_contents($filename));
    $yaml = Yaml::dump($expanded_config, 3, 2);
    file_put_contents($filename, $yaml);
  }

}
