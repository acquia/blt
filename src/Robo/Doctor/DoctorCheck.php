<?php

namespace Acquia\Blt\Robo\Doctor;

use Acquia\Blt\Robo\Common\Executor;
use Acquia\Blt\Robo\Common\ExecutorAwareInterface;
use Acquia\Blt\Robo\Common\ExecutorAwareTrait;
use Acquia\Blt\Robo\Config\ConfigAwareTrait;
use Acquia\Blt\Robo\Inspector\Inspector;
use Acquia\Blt\Robo\Inspector\InspectorAwareInterface;
use Acquia\Blt\Robo\Inspector\InspectorAwareTrait;
use Robo\Config\Config;
use Robo\Contract\ConfigAwareInterface;

/**
 * BLT Doctor checks.
 */
abstract class DoctorCheck implements ConfigAwareInterface, InspectorAwareInterface, ExecutorAwareInterface {
  use ConfigAwareTrait;
  use InspectorAwareTrait;
  use ExecutorAwareTrait;

  /**
   * Problems.
   *
   * @var array
   */
  protected $problems = [];

  /**
   * Whether an error was logged.
   *
   * @var bool
   */
  protected $errorLogged = FALSE;

  /**
   * Drush status.
   *
   * @var string
   */
  protected $drushStatus;

  /**
   * Constructor.
   */
  public function __construct(
    Config $config,
    Inspector $inspector,
    Executor $executor,
    $drush_status
  ) {
    $this->setConfig($config);
    $this->setInspector($inspector);
    $this->drushStatus = $drush_status;
    $this->executor = $executor;
  }

  /**
   * Log problem.
   */
  public function logProblem($check, $message, $type) {
    if (is_array($message)) {
      $message = implode("\n", $message);
    }
    $reflection = new \ReflectionClass($this);
    $class_name = $reflection->getShortName();
    $label = "<$type>$class_name:$check</$type>";
    $this->problems[$label] = $message;

    if ($type == 'error') {
      $this->errorLogged = TRUE;
    }
  }

  /**
   * Was error logged.
   *
   * @return bool
   *   Was error logged.
   */
  public function wasErrorLogged() {
    return $this->errorLogged;
  }

  /**
   * Perform all checks.
   *
   * @return array
   *   Array.
   */
  abstract public function performAllChecks();

  /**
   * Get problems.
   *
   * @return array
   *   Problems?
   */
  public function getProblems() {
    return $this->problems;
  }

}
