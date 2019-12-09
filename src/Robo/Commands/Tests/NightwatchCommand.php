<?php

namespace Acquia\Blt\Robo\Commands\Tests;

/**
 * Defines commands in the "tests" namespace.
 */
class NightwatchCommand extends DrupalTestCommand {

  /**
   * Setup and run Nightwatch tests.
   *
   * @command tests:drupal:nightwatch:run
   * @description Executes all Drupal Nightwatch tests. Launches chromedriver prior to execution.
   * @hidden
   *
   * @throws \Exception
   *   Throws an exception if any test fails.
   */
  public function runDrupalTests() {
    $this->reportsDir = $this->getConfigValue('tests.reports.localDir') . '/drupal/nightwatch';
    // Normally we'd call parent::run() here, but we don't need to start or stop
    // any web drivers so we override it.
    $this->setTestingConfig();
    $this->getTestingEnvString();
    $this->createLogs();
    $this->executeTests();
  }

  /**
   * Executes the Drupal Nightwatch tests.
   */
  public function executeTests() {
    $drupal_core_dir = $this->getConfigValue('docroot') . '/core';
    $this->taskFilesystemStack()
      ->copy($drupal_core_dir . '/.env.example', $drupal_core_dir . '/.env')
      ->run();
    $this->taskExec('yarn install')
      ->dir($drupal_core_dir)
      ->run();
    $this->taskExec('yarn test:nightwatch --env local')
      ->dir($drupal_core_dir)
      ->env($this->testingEnv)
      ->run();
  }

}
