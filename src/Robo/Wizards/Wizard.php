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
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class Wizard.
 *
 * @package Acquia\Blt\Robo\Wizards
 *
 * This class should be used as the super class for all Wizards.
 *
 * Wizards should take the following form:
 *   1. Evaluate a condition via an Inspector method.
 *   2. Prompt the the user to resolve invalid configuration or state.
 *   3. Perform tasks to resolve the issue.
 */
abstract class Wizard implements ConfigAwareInterface, InspectorAwareInterface, IOAwareInterface, LoggerAwareInterface {

  use ConfigAwareTrait;
  use InspectorAwareTrait;
  use IO;
  use LoggerAwareTrait;

  /**
   * Process Executor.
   *
   * @var \Acquia\Blt\Robo\Common\Executor
   */
  protected $executor;

  /**
   * File system component.
   *
   * @var \Symfony\Component\Filesystem\Filesystem
   */
  protected $fs;

  /**
   * Inspector constructor.
   *
   * @param \Acquia\Blt\Robo\Common\Executor $executor
   *   Process executor.
   */
  public function __construct(Executor $executor) {
    $this->executor = $executor;
    $this->fs = new Filesystem();
  }

}
