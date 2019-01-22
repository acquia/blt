<?php

namespace Acquia\Blt\Robo\Tasks;

use Robo\Task\Testing\PHPUnit;

/**
 * Runs PHPUnit tests.
 */
class PhpUnitTask extends PHPUnit {

  /**
   * @var string
   */
  protected $testEnvVars;

  /**
   * @var bool
   */
  protected $sudo;

  /**
   * @var string
   */
  protected $user;

  /**
   * @return $this
   */
  public function testEnvVars($testEnvVars) {
    $this->testEnvVars = is_string($testEnvVars) ? $testEnvVars : NULL;
    return $this;
  }

  /**
   * @return $this
   */
  public function sudo(bool $sudo = TRUE) {
    $this->sudo = $sudo;
    return $this;
  }

  /**
   * @return $this
   */
  public function user($user) {
    $this->user = is_string($user) ? $user : NULL;
    return $this;
  }

  /**
   * @param string $printer
   *
   * @return $this
   */
  public function printer($printer) {
    $this->option('printer', $printer);
    return $this;
  }

  /**
   * @return $this
   */
  public function stopOnError() {
    $this->option("stop-on-error");
    return $this;
  }

  /**
   * @return $this
   */
  public function stopOnFailure() {
    $this->option("stop-on-failure");
    return $this;
  }

  /**
   * @return $this
   */
  public function testdox() {
    $this->option("testdox");
    return $this;
  }

  /**
   * @param string $testsuites
   *
   * @return $this
   */
  public function testsuite($testsuites) {
    $this->option('testsuite', $testsuites, ' ');
    return $this;
  }

  /**
   * @return $this
   */
  public function verbose() {
    $this->option("verbose");
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCommand() {
    $env = isset($this->testEnvVars) ? "$this->testEnvVars " : "";
    $command = $this->command . $this->arguments . $this->files;
    $sudo = isset($this->user) && $this->sudo ? "sudo -u $this->user -E " : "";
    return $sudo ? $env . $sudo . $command : $env . $command;
  }

}
