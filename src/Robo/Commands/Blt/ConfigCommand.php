<?php

namespace Acquia\Blt\Robo\Commands\Blt;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Drupal\RecommendedSettings\Exceptions\SettingsException;

/**
 * Defines commands in the 'config:*' namespace.
 */
class ConfigCommand extends BltTasks {

  /**
   * Gets the value of a config variable.
   *
   * @param string $key
   *   The key for the configuration item to get.
   *
   * @command blt:config:get
   *
   * @aliases bcg config:get
   *
   * @throws \Acquia\Drupal\RecommendedSettings\Exceptions\SettingsException
   */
  public function getValue($key) {
    if (!$this->getConfig()->has($key)) {
      throw new BltException("$key is not set.");
    }

    $this->say($this->getConfigValue($key));
  }

  /**
   * Dumps all configuration values.
   *
   * @command blt:config:dump
   *
   * @aliases bcd dump config:dump
   */
  public function dump() {
    $config = $this->getConfig()->export();
    ksort($config);
    $this->printArrayAsTable($config);
  }

}
