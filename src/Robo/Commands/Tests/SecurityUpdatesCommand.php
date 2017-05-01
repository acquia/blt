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
    $result = $this->taskDrush()
      ->assume('')
      ->uri('')
      ->drush("-n ups --check-disabled --security-only 2>/dev/null | grep 'SECURITY UPDATE'")
      ->printOutput(FALSE)
      ->printMetadata(FALSE)
      ->run();

    $passed = !$result->wasSuccessful();
    $output = $result->getOutputData();

    if (!$passed) {
      $this->logger->error("One or more of your dependencies has an outstanding security update. Please apply update(s) immediately.");
      $this->say($output);
      $this->logger->notice('To disable security checks, set disable-targets.tests.security-updates to false in project.yml.');

      return 1;
    }
    else {
      $this->writeln("<info>There are no outstanding security updates for Drupal projects.</info>");

      return 0;
    }
  }

}
