<?php

namespace Acquia\Blt\Robo\Commands\Blt;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;
use Robo\Contract\VerbosityThresholdInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Defines commands in the "blt:doctor" namespace.
 */
class DoctorCommand extends BltTasks {

  /**
   * Inspects your local blt configuration for possible issues.
   *
   * @command doctor
   */
  public function doctor() {

    $this->taskDrush()
      ->drush('cc drush')
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();

    // Attempt to run BLT doctor inside of a VM.
    if ($this->getInspector()->isDrupalVmLocallyInitialized()
      && $this->getInspector()->isDrupalVmBooted()
      && !$this->getInspector()->isVmCli()) {
      $result = $this->executeDoctorInsideVm();
      if ($result->wasSuccessful()) {
        return $result->getExitCode();
      }
    }

    // Try BLT doctor with default alias. This might be a Drupal VM alias.
    $alias = $this->getConfigValue('drush.alias');
    $this->say('Attempting to run doctor on host machine...');
    $result = $this->executeDoctorOnHost($alias);

    // If default alias failed, try again using @self alias.
    if (!$result->wasSuccessful() && $alias != 'self') {
      $this->logger->warning("Unable to run the doctor using alias '@$alias'. Trying with '@self'...");
      $this->executeDoctorOnHost('self');
    }

    // If @self fails, try without any alias.
    if (!$result->wasSuccessful() && $alias != '') {
      $this->logger->warning("Unable to run the doctor using alias '@self'. Trying without alias...");
      $this->executeDoctorOnHost('');
    }

    if (!$result->wasSuccessful()) {
      throw new BltException("Unable to execute the `blt doctor` command.");
    }

    return $result->getMessage();
  }

  /**
   * Executes `blt doctor` inside Drupal VM.
   *
   * @return \Robo\Result
   *   The command result.
   *
   * @throws \Exception
   */
  protected function executeDoctorInsideVm() {
    $drupal_vm_config_filepath = $this->getConfigValue('vm.config');
    $drupal_vm_config = Yaml::parse(file_get_contents($drupal_vm_config_filepath));
    $repo_root = $drupal_vm_config['drupal_core_path'] . '/..';
    if (strstr($repo_root, '{{')) {
      $this->logger->error("The value of drupal_core_path in $drupal_vm_config_filepath contains an unresolved Ansible variable.");
      $this->logger->error("Do not use Ansible variable placeholders for drupal_core_path.");
      $this->logger->error("drupal_core_path is currently '$repo_root'. Please correct it.");
      throw new BltException("Unparsable value in $drupal_vm_config_filepath.");
    }

    $this->say("Drupal VM was detected. Running blt doctor inside of VM...");
    $command = "cd $repo_root && $repo_root/vendor/bin/drush cc drush && $repo_root/vendor/bin/drush --include=$repo_root/vendor/acquia/blt/drush blt-doctor -r $repo_root/docroot";

    return $this->executeCommandInDrupalVm($command);
  }

  /**
   * Executes `blt doctor` on host machine.
   *
   * @return \Robo\Result
   *   The command result.
   */
  protected function executeDoctorOnHost($alias) {
    $result = $this->taskDrush()
      ->drush("blt-doctor")
      ->alias($alias)
      ->uri("")
      ->includePath($this->getConfigValue('blt.root') . '/drush')
      ->printOutput(FALSE)
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();

    return $result;
  }

}
