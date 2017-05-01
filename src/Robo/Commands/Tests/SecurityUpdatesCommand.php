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
   * @command tests:security-updates
   * @description Check local Drupal installation for security updates.
   *
   * @interactInstallDrupal
   * @validateDrupalIsInstalled
   */
  public function testsSecurityUpdates() {
    /** @var \Robo\ResultData $result */
    $result = $this->taskDrush()
      ->drush("-n ups --check-disabled --security-only 2>/dev/null | grep 'SECURITY UPDATE'")
      ->run();
    $passed = !$result->wasSuccessful();
    $output = $result->getOutputData();

    if (!$passed) {
      $this->logger->error("One or more of your dependencies has an outstanding security update. Please apply update(s) immediately.");
      $this->logger->notice($output);
      $this->logger->notice('To disable security checks, set `disable-targets.tests.security-updates` to `false` in project.yml.');
    }
    else {
      $this->writeln("<info>There are no outstanding security updates for Drupal projects.</info>");
    }
  }

}
