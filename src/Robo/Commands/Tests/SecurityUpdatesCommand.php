<?php

namespace Acquia\Blt\Robo\Commands\Tests;

use Acquia\Blt\Robo\BltTasks;
use Robo\Contract\VerbosityThresholdInterface;
use Symfony\Component\Console\Helper\Table;

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
      ->assume(FALSE)
      ->uri('')
      ->drush("ups --check-disabled --security-only --format=json")
      ->verbose(FALSE)
      ->printOutput(FALSE)
      ->printMetadata(TRUE)
      ->interactive(FALSE)
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERY_VERBOSE)
      ->run();

    $output = $result->getMessage();
    $report = substr($output, strpos($output, '{'));
    $updates = (array) json_decode($report, TRUE);
    $security_updates = [];
    foreach ($updates as $name => $project) {
      if (!empty($project['security updates'])) {
        $security_updates[$name] = [
          $name,
          $project['existing_version'],
          $project['security updates'][0]['version'],
        ];
      }
    }

    if ($security_updates) {
      $this->logger->error("One or more of your dependencies has an outstanding security update. Please apply update(s) immediately.");
      $table = new Table($this->output);
      $table->setHeaders(['Name', 'Installed version', 'Suggested version'])
        ->setRows($security_updates)
        ->render();
      $this->logger->notice('To disable security checks, set disable-targets.tests.security-updates to false in project.yml.');

      return 1;
    }
    else {
      $this->writeln("<info>There are no outstanding security updates for Drupal projects.</info>");

      return 0;
    }
  }

}
