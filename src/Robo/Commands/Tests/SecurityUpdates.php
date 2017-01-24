<?php

namespace Acquia\Blt\Robo\Commands\Tests;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Wizards\TestsWizard;
use Drupal\Core\Database\Log;
use GuzzleHttp\Client;
use Psr\Log\LogLevel;
use Wikimedia\WaitConditionLoop;

/**
 * Defines commands in the "tests" namespace.
 */
class SecurityUpdates extends BltTasks {

  /**
   * Check local Drupal installation for security updates.
   *
   * @command tests:security-updates
   * @description Check local Drupal installation for security updates.
   */
  public function testsSecurityUpdates() {
    $passed = $this->taskExec("! drush -n ups --check-disabled --security-only 2>/dev/null | grep 'SECURITY UPDATE'")
      ->run()->wasSuccessful();
    if ($passed) {
      $this->logger->error("One or more of your dependency has an outstanding security update. Please apply update(s) immediately.");
    }
  }

}
