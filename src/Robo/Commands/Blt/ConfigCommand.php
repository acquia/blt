<?php

namespace Acquia\Blt\Robo\Commands\Blt;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;

/**
 * Defines commands in the 'config:*' namespace.
 */
class ConfigCommand extends BltTasks {

  /**
   * Gets the value of a config variable.
   *
   * @command config:get
   *
   * @param string $key
   *   The key for the configuration item to get.
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
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
   * @command config:dump
   */
  public function dump() {
    $config = $this->getConfig()->export();
    $this->printArrayAsTable($config);
  }

}
