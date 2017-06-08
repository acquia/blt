<?php

namespace Acquia\Blt\Robo\Commands\Acsf;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;
use Robo\Contract\VerbosityThresholdInterface;

/**
 * Defines commands in the "acsf" namespace.
 */
class AcsfCommand extends BltTasks {

  /**
   * Initializes ACSF support for project.
   *
   * @command acsf:init
   *
   * @options acsf-version
   */
  public function acsfInitialize($options = ['acsf-version' => '^1.33.0']) {
    $this->acsfHooksInitialize();
    $this->say('Adding acsf module as a dependency...');
    $this->requireAcsf($options['acsf-version']);
    $this->say("In the future, you may pass in a custom value for acsf-version to override the default version. E.g., blt acsf:init --acsf-version='8.1.x-dev'");
    $this->acsfDrushInitialize();
    $this->say("<info>ACSF was successfully initialized.</info>");
  }

  /**
   * Refreshes the ACSF settings and hook files.
   *
   * @command acsf:init:drush
   */
  public function acsfDrushInitialize() {
    $this->say('Executing initialization command provided acsf module...');

    $result = $this->taskDrush()
      ->drush('acsf-init')
      ->alias("")
      ->includePath("{$this->getConfigValue('docroot')}/modules/contrib/acsf/acsf_init")
      ->run();

    $this->say('<comment>Please add acsf_init as a dependency for your installation profile to ensure that it remains enabled.</comment>');
    $this->say('<comment>An example alias file for ACSF is located in /drush/site-aliases/example.acsf.aliases.drushrc.php.</comment>');

    return $result;
  }

  /**
   * Creates "factory-hooks/" directory in project's repo root.
   */
  protected function acsfHooksInitialize() {
    $defaultAcsfHooks = $this->getConfigValue('blt.root') . '/settings/acsf';
    $projectAcsfHooks = $this->getConfigValue('repo.root') . '/factory-hooks';

    $result = $this->taskCopyDir([$defaultAcsfHooks => $projectAcsfHooks])
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();

    if (!$result->wasSuccessful()) {
      throw new BltException("Unable to copy ACSF scripts.");
    }

    $this->say('New "factory-hooks/" directory created in repo root. Please commit this to your project.');

    return $result;
  }

  /**
   * Installs drupal/acsf via Composer.
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  protected function requireAcsf($acsfVersion) {
    $result = $this->taskExec("composer require 'drupal/acsf:{$acsfVersion}'")
      ->printOutput(TRUE)
      ->dir($this->getConfigValue('repo.root'))
      ->run();

    if (!$result->wasSuccessful()) {
      $this->logger->error("An error occurred while requiring drupal/acsf.");
      $this->say("This is likely due to an incompatibility with your existing packages.");
      $confirm = $this->confirm("Should BLT attempt to update all of your Composer packages in order to find a compatible version?");
      if ($confirm) {
        $result = $this->taskExec("composer require 'drupal/acsf:{$acsfVersion}' --no-update && composer update")
          ->printOutput(TRUE)
          ->dir($this->getConfigValue('repo.root'))
          ->run();
        if (!$result->wasSuccessful()) {
          throw new BltException("Unable to install drupal/acsf package.");
        }
      }
      else {
        // @todo revert previous file chanages.
        throw new BltException("Unable to install drupal/acsf package.");
      }
    }
  }

}
