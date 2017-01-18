<?php

namespace Acquia\Blt\Robo\Config;

use Robo\Common\ConfigAwareTrait as RoboConfigAwareTrait;

trait ConfigAwareTrait {

  use RoboConfigAwareTrait;

  /**
   * @param string $key
   * @param mixed|null $default
   *
   * @return mixed|null
   */
  protected function getConfigValue($key, $default = null)
  {
    if (!$this->getConfig()) {
      return $default;
    }
    return $this->getConfig()->get($key, $default);
  }
}
