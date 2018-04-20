<?php

namespace Acquia\Blt\Robo\Commands\Acsf;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Common\YamlMunge;
use Acquia\Blt\Robo\Exceptions\BltException;
use Robo\Contract\VerbosityThresholdInterface;

/**
 * Defines commands in the "acsf" namespace.
 */
class AcsfCommand extends BltTasks {

  /**
   * Prints information about the command.
   */
  public function printPreamble() {
    $this->logger->notice("This command will initialize support for Acquia Cloud Site Factory by performing the following tasks:");
    $this->logger->notice("  * Adding drupal/acsf and acquia/acsf-tools the require array in your composer.json file.");
    $this->logger->notice("  * Executing the `acsf-init` command, provided by the drupal/acsf module.");
    $this->logger->notice("  * Adding default factory-hooks to your application.");
    $this->logger->notice("  * Adding `acsf` to `modules.local.uninstall` in your blt.yml");
    $this->logger->notice("");
    $this->logger->notice("For more information, see:");
    $this->logger->notice("<comment>http://blt.readthedocs.io/en/9.x/readme/acsf-setup</comment>");
  }

  /**
   * Initializes ACSF support for project.
   *
   * @command recipes:acsf:init:all
   *
   * @aliases acsf acsf:init
   * @options acsf-version
   */
  public function acsfInitialize() {
    $this->printPreamble();
    $this->acsfHooksInitialize();
    $this->say('Adding acsf module as a dependency...');
    $this->acsfDrushInitialize();
    $this->say('Adding acsf-tools drush module as a dependency...');
    $result = $this->taskExecStack()
      ->dir($this->getConfigValue('repo.root'))
      ->exec("composer bin drush-8 require acquia/acsf-tools")
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();
    if (!$result->wasSuccessful()) {
      throw new BltException("Unable to require acquia/acsf-tools");
    }
    $this->invokeCommand('internal:composer:require', $package_options);
    $this->say('<comment>ACSF Tools has been added. Some post-install configuration is necessary.</comment>');
    $this->say('<comment>See /drush/contrib/acsf-tools/README.md. </comment>');
    $this->say('<info>ACSF was successfully initialized.</info>');
    $project_yml = $this->getConfigValue('blt.config-files.project');
    $project_config = YamlMunge::parseFile($project_yml);
    if (!empty($project_config['modules'])) {
      $project_config['modules']['local']['uninstall'][] = 'acsf';
    }
    YamlMunge::writeFile($project_yml, $project_config);
  }

  /**
   * Refreshes the ACSF settings and hook files.
   *
   * @command recipes:acsf:init:drush
   *
   * @aliases raid acsf:init:drush
   */
  public function acsfDrushInitialize() {
    $drush8 = $this->getConfigValue('repo.root') . '/vendor-bin/drush-8/vendor/bin/drush';
    $this->say('Executing initialization command provided by acsf module...');
    $acsf_include = $this->getConfigValue('docroot') . '/modules/contrib/acsf/acsf_init';
    $result = $this->taskExecStack()
      ->exec("$drush8 acsf-init --include=\"$acsf_include\" --root=\"{$this->getConfigValue('docroot')}\" -y")
      ->run();

    if (!$result->wasSuccessful()) {
      throw new BltException("Unable to copy ACSF scripts.");
    }

    return $result;
  }

  /**
   * Creates "factory-hooks/" directory in project's repo root.
   *
   * @command recipes:acsf:init:hooks
   * @aliases raih
   */
  public function acsfHooksInitialize() {
    $defaultAcsfHooks = $this->getConfigValue('blt.root') . '/scripts/factory-hooks';
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
