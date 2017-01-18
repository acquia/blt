<?php

namespace Acquia\Blt\Robo\Config;

use Acquia\Blt\Robo\Common\ArrayManipulator;
use Grasmash\YamlExpander\Expander;

/**
 *
 */
class YamlConfig extends BltConfig {

  /**
   * YamlConfig constructor.
   *
   * @param string $yml_path
   *   The path to the yaml file.
   */
  public function __construct($yml_path, $reference_data = []) {
    parent::__construct();

    $this->setSourceName($yml_path);
    $reference_data = ArrayManipulator::reKeyDotNotatedKeys($reference_data);
    $file_config = file_exists($yml_path) ? Expander::parse(file_get_contents($yml_path), $reference_data) : [];
    $file_config = ArrayManipulator::reKeyDotNotatedKeys($file_config);
    $this->fromArray($file_config);
  }

}
