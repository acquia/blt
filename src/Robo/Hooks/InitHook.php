<?php

namespace Acquia\Blt\Robo\Hooks;

use Acquia\Blt\Robo\Config\ConfigAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Robo\Contract\ConfigAwareInterface;
use Robo\Contract\IOAwareInterface;
use Robo\Tasks;

/**
 * This class defines hooks that provide user interaction.
 *
 * These hooks typically use a Wizard to evaluate the validity of config or
 * state and guide the user toward resolving issues.
 */
class InitHook extends Tasks implements IOAwareInterface, ConfigAwareInterface, LoggerAwareInterface {

  use ConfigAwareTrait;
  use LoggerAwareTrait;

  /**
   * This hook will fire for all commands.
   *
   * @hook init *
   */
  public function initialize() {
    // We set the value for site late in the bootstrap process so that a user
    // may define its value at runtime via --define. This can only happen after
    // input has been processed.
    $multisites = $this->getConfigValue('multisites');
    $first_multisite = reset($multisites);
    $site = $this->getConfigValue('site', $first_multisite);
    $this->getConfig()->setSiteConfig($site);
  }

}
