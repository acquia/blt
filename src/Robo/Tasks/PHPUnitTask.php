<?php

namespace Acquia\Blt\Robo\Tasks;

use Robo\Contract\VerbosityThresholdInterface;
use Robo\Task\Testing\PHPUnit;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Runs PHPUnit tests.
 */
class PhpUnitTask extends PHPUnit {

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

}
