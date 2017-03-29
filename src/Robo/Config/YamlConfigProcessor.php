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
    $config = ArrayManipulator::expandFromDotNotatedKeys($config);

    return $config;
  }

  /**
   * @{inheritdoc}
   */
  public function export() {
    $processed_config = parent::export();
    // It's possible that the already processed config has unexpanded
    // placeholders for which the new config provides values. In that case, we
    // must re-expand the already processed config.
    $this->processedConfig = Expander::expandArrayProperties($processed_config);

    return $this->processedConfig;
  }



}
