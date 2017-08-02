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
    $package_options = [
      'package_name' => 'drupal/acsf',
      'package_version' => $options['acsf-version'],
    ];
    $this->invokeCommand('composer:require', $package_options);
    $this->say("In the future, you may pass in a custom value for acsf-version to override the default version. E.g., blt acsf:init --acsf-version='8.1.x-dev'");
    $this->acsfDrushInitialize();
    $this->say('Adding acsf-tools drush module as a dependency...');
    $package_options = [
      'package_name' => 'acquia/acsf-tools',
      'package_version' => '^8.1',
    ];
    $this->invokeCommand('composer:require', $package_options);
    $this->say('<comment>ACSF Tools has been added. Some post-install configuration is necessary.</comment>');
    $this->say('<comment>See /drush/contrib/acsf-tools/README.md. </comment>');
    $this->say('<info>ACSF was successfully initialized.</info>');
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

}
