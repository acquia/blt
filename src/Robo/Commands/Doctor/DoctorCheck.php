<?php

namespace Acquia\Blt\Robo\Commands\Doctor;

use Acquia\Blt\Robo\Common\Executor;
use Acquia\Blt\Robo\Common\ExecutorAwareInterface;
use Acquia\Blt\Robo\Common\ExecutorAwareTrait;
use Acquia\Blt\Robo\Config\ConfigAwareTrait;
use Acquia\Blt\Robo\Inspector\Inspector;
use Acquia\Blt\Robo\Inspector\InspectorAwareInterface;
use Acquia\Blt\Robo\Inspector\InspectorAwareTrait;
use function is_array;
use ReflectionClass;
use Robo\Config\Config;
use Robo\Contract\ConfigAwareInterface;

/**
 *
 */
abstract class DoctorCheck implements ConfigAwareInterface, InspectorAwareInterface, ExecutorAwareInterface {
  use ConfigAwareTrait;
  use InspectorAwareTrait;
  use ExecutorAwareTrait;

  protected $problems = [];
  protected $errorLogged = FALSE;
  protected $drushStatus;

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

  public function logProblem($check, $message, $type) {
    if (is_array($message)) {
      $message = implode("\n", $message);
    }
    $reflection = new ReflectionClass($this);
    $class_name = $reflection->getShortName();
    $label = "<$type>$class_name:$check</$type>";
    $this->problems[$label] = $message;

    if ($type == 'error') {
      $this->errorLogged = TRUE;
    }
  }

  /**
   * @return bool
   */
  public function wasErrorLogged() {
    return $this->errorLogged;
  }

  /**
   * @return array
   */
  abstract public function performAllChecks();

  /**
   * @return array
   */
  public function getProblems() {
    return $this->problems;
  }

}
