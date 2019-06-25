<?php

namespace Acquia\Blt\Robo\Tasks;

use Robo\Task\Testing\PHPUnit;

/**
 * Runs PHPUnit tests.
 */
class PhpUnitTask extends PHPUnit {

  /**
   * Test env vars.
   *
   * @var string
   */
  protected $testEnvVars;

  /**
   * Sudo.
   *
   * @var bool
   */
  protected $sudo;

  /**
   * User.
   *
   * @var string
   */
  protected $user;

  /**
   * Test env vars.
   *
   * @return $this
   */
  public function testEnvVars($testEnvVars) {
    $this->testEnvVars = is_string($testEnvVars) ? $testEnvVars : NULL;
    return $this;
  }

  /**
   * Sudo.
   *
   * @return $this
   */
  public function sudo(bool $sudo = TRUE) {
    $this->sudo = $sudo;
    return $this;
  }

  /**
   * User.
   *
   * @return $this
   */
  public function user($user) {
    $this->user = is_string($user) ? $user : NULL;
    return $this;
  }

  /**
   * Printer.
   *
   * @param string $printer
   *   Printer.
   *
   * @return $this
   */
  public function printer($printer) {
    $this->option('printer', $printer);
    return $this;
  }

  /**
   * Stop on error.
   *
   * @return $this
   */
  public function stopOnError() {
    $this->option("stop-on-error");
    return $this;
  }

  /**
   * Stop on failure.
   *
   * @return $this
   */
  public function stopOnFailure() {
    $this->option("stop-on-failure");
    return $this;
  }

  /**
   * Test dox.
   *
   * @return $this
   */
  public function testdox() {
    $this->option("testdox");
    return $this;
  }

  /**
   * Test suites.
   *
   * @param string $testsuites
   *   Test suites.
   *
   * @return $this
   */
  public function testsuite($testsuites) {
    $this->option('testsuite', $testsuites, ' ');
    return $this;
  }

  /**
   * Verbose.
   *
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
