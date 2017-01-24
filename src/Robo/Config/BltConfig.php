<?php

namespace Acquia\Blt\Robo\Config;

use Acquia\Blt\Robo\Common\ArrayManipulator;
use Dflydev\DotAccessData\Data;
use Grasmash\YamlExpander\Expander;
use Robo\Config;

/**
 *
 */
abstract class BltConfig extends Config {

  /**
   * @var Data
   */
  protected $data;

  /**
   * @var array
   */
  protected $sources = [];

  /**
   * @var string
   */
  protected $source_name = 'Unknown';

  /**
   *
   */
  public function __construct() {
    $this->data = new Data();
  }

  /**
   * Fet a configuration value.
   *
   * @param string $key
   *   Which config item to look up.
   * @param string|null $defaultOverride
   *   Override usual default value with a different default.
   *
   * @return mixed
   */
  public function get($key, $defaultOverride = NULL) {
    if ($this->data->has($key)) {
      return $this->data->get($key);
    }
    return $this->getDefault($key, $defaultOverride);
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
    $this->data->set($key, $value);

    return $this;
  }

  /**
   * Set add all the values in the array to this Config object.
   *
   * @param array $array
   */
  public function fromArray($array) {
    $this->data->import($array);
  }

  /**
   * Override the values in this Config object with the given input Config.
   *
   * @param BltConfig $in
   */
  public function extend(BltConfig $in) {
    $base_config = $this->toArray();

    $new_config = $in->toArray();
    $merged_config = ArrayManipulator::arrayMergeRecursiveDistinct($base_config,
      $new_config);

    // When the new config was created, it was expanded using base config as
    // a reference. But, the base config may have had unexpanded placeholders
    // whose values were present in the new config. So, we self-expand
    // the newly merged config to resolve all placeholders.
    $merged_config = Expander::expandArrayProperties($merged_config);

    $this->fromArray($merged_config);
  }

  /**
   * Return all of the keys in the Config.
   *
   * @return array
   */
  public function keys() {
    return array_keys($this->data->export());
  }

  /**
   * Get a description of where this configuration came from.
   *
   * @param $key
   *
   * @return string
   */
  public function getSource($key) {
    return isset($this->sources[$key]) ? $this->sources[$key] : $this->getSourceName();
  }

  /**
   * Set the source for a given configuration item.
   *
   * @param $key
   * @param $source
   */
  protected function setSource($key, $source) {
    $this->sources[$key] = $source;
  }

  /**
   * Get the name of the source for this configuration object.
   *
   * @return string
   */
  public function getSourceName() {
    return $this->source_name;
  }

  /**
   * @param mixed $source_name
   */
  public function setSourceName($source_name) {
    $this->source_name = $source_name;
  }

  /**
   * Convert the config to an array.
   *
   * @return array
   */
  public function toArray() {
    return $this->data->export();
  }

}
