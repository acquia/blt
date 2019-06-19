<?php

namespace Acquia\Blt\Robo\Commands\Tests;

use Acquia\Blt\Robo\BltTasks;

/**
 * Defines commands in the "tests" namespace.
 */
class SecurityUpdatesCommand extends BltTasks {

  /**
   * Check local Drupal installation for security updates.
   *
   * @command tests:security:check:updates
   * @aliases tscu security tests:security-updates
   * @executeInVm
   */
  public function testsSecurityUpdates() {
    $result = $this->taskDrush()
      ->drush("pm:security")
      ->run();

    if ($result->getExitCode()) {
      $this->logger->notice('To disable security checks, set disable-targets.tests.security.check.updates to true in blt.yml.');
      return 1;
    }
    else {
      $this->writeln("<info>There are no outstanding security updates for Drupal projects.</info>");
      return 0;
    }
  }

  /**
   * Check composer.lock for security updates.
   *
   * @command tests:security:check:composer
   * @aliases tscom security tests:composer
   * @executeInVm
   */
  public function testsSecurityComposer() {
    $bin = $this->getConfigValue('composer.bin');
    $result = $this->taskExecStack()
      ->dir($this->getConfigValue('repo.root'))
      ->exec("$bin/security-checker security:check composer.lock")
      ->run();

    if ($result->getExitCode()) {
      $this->logger->notice('To disable security checks, set disable-targets.tests.security.check.composer to true in blt.yml.');
      return 1;
    }
    else {
      $this->writeln("<info>There are no outstanding security updates for your composer packages.</info>");
      return 0;
    }
  }

}
