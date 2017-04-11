<?php

namespace Acquia\Blt\Robo\Config;

use Acquia\Blt\Robo\Common\ArrayManipulator;
use Grasmash\YamlExpander\Expander;
use Robo\Config\ConfigProcessor;

class YamlConfigProcessor extends ConfigProcessor {

  /**
   * Expand dot notated keys.
   *
   * @param array $config
   * @return array
   */
  protected function preprocess($config)
  {
    $config = ArrayManipulator::expandFromDotNotatedKeys(ArrayManipulator::flattenToDotNotatedKeys($config));

    return $config;
  }



}
