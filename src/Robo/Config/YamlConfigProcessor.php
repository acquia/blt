<?php

namespace Acquia\Blt\Robo\Config;

use Acquia\Blt\Robo\Common\ArrayManipulator;
use Robo\Config\ConfigProcessor;

/**
 * Custom processor for YAML based configration.
 */
class YamlConfigProcessor extends ConfigProcessor {

  /**
   * Expand dot notated keys.
   *
   * @param array $config
   *   The configuration to be processed.
   *
   * @return array
   *   The processed configuration
   */
  protected function preprocess($config) {
    $config = ArrayManipulator::expandFromDotNotatedKeys(ArrayManipulator::flattenToDotNotatedKeys($config));

    return $config;
  }

}
