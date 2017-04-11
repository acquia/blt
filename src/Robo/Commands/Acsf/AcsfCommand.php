<?php

namespace Acquia\Blt\Robo\Commands\Acsf;

use Acquia\Blt\Robo\BltTasks;
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
   * @options acsf-package
   */
  public function acsfInitialize($options = ['acsf-package' => 'drupal/acsf:^1.33.0']) {
    $this->acsfHooksInitialize();

    $this->say('Adding acsf module as a dependency.');
    $this->requireAcsf($options['acsf-package']);

    $this->say("In the future, you may pass a custom value for acsf.package to override default version. E.g., blt acsf:init --acsf.package='drupal/acsf:8.1.x-dev'");

    $this->acsfDrushInitialize();
  }

  /**
   * Refreshes the ACSF settings and hook files.
   *
   * @command acsf:init:drush
   */
  public function acsfDrushInitialize() {
    $drushBin = $this->getConfigValue('drush.bin');
    $drushAlias = $this->getConfigValue('drush.default_alias');

    $this->say('Executing initialization command for acsf module.');

    $this->taskExec("{$drushBin} @{$drushAlias} acsf-init --include={$this->getConfigValue('docroot')}/modules/contrib/acsf/acsf_init")
      ->printOutput(TRUE)
      ->dir($this->getConfigValue('docroot'))
      ->run();

    $this->say('Please add acsf_init as a dependency for your installation profile to ensure that it remains enabled.');
    $this->say('An example alias file for ACSF is located in /drush/site-aliases/example.acsf.aliases.drushrc.php.');
  }

  /**
   * Creates "factory-hooks/" directory in project's repo root.
   */
  protected function acsfHooksInitialize() {
    $defaultAcsfHooks = $this->getConfigValue('blt.root') . '/settings/acsf';
    $projectAcsfHooks = $this->getConfigValue('repo.root') . '/factory-hooks';

    $this->taskCopyDir([$defaultAcsfHooks => $projectAcsfHooks])
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();

    $this->say('New "factory-hooks/" directory created in repo root. Please commit this to your project.');
  }

  /**
   * Installs drupal/acsf via Composer.
   *
   * @throws \Exception
   */
  protected function requireAcsf($acsfPackage) {
    $result = $this->taskExec("composer require {$acsfPackage}")
      ->interactive()
      ->printOutput(TRUE)
      ->dir($this->getConfigValue('repo.root'))
      ->run();

    if (!$result->wasSuccessful()) {
      $this->logger->error("An error occurred while requiring drupal/acsf.");
      $this->say("This is likely due to an incompatibility with your existing packages.");
      $confirm = $this->confirm("Should BLT attempt to update all of your Composer packages in order to find a compatible version?");
      if ($confirm) {
        $result = $this->taskExec("composer require {$acsfPackage} --no-update && composer update")
          ->interactive()
          ->printOutput(TRUE)
          ->dir($this->getConfigValue('repo.root'))
          ->run();
      }
      else {
        // @todo revert previous file chanages.
        throw new \Exception("Unable to install acsf");
      }
    }
  }

}
