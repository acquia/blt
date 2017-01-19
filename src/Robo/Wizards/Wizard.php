<?php

namespace Acquia\Blt\Robo\Wizards;

use Acquia\Blt\Robo\Common\Executor;
use Acquia\Blt\Robo\Common\IO;
use Acquia\Blt\Robo\Config\ConfigAwareTrait;
use Acquia\Blt\Robo\Inspector\InspectorAwareInterface;
use Acquia\Blt\Robo\Inspector\InspectorAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Robo\Contract\ConfigAwareInterface;
use Robo\Contract\IOAwareInterface;

/**
 * Class Wizard
 * @package Acquia\Blt\Robo\Wizards
 */
abstract class Wizard implements ConfigAwareInterface, InspectorAwareInterface, IOAwareInterface, LoggerAwareInterface {

  /** @var Executor */
  protected $executor;

  /**
   * Inspector constructor.
   *
   * @param \Acquia\Blt\Robo\Common\Executor $executor
   */
  public function __construct(Executor $executor) {
    $this->executor = $executor;
  }

  use ConfigAwareTrait;
  use InspectorAwareTrait;
  use IO;
  use LoggerAwareTrait;
}
