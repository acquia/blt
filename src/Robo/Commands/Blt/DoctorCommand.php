<?php

namespace Acquia\Blt\Robo\Commands\Blt;

use Acquia\Blt\Robo\BltTasks;
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

    return $result->getExitCode();
  }

  /**
   * Executes `blt doctor` inside Drupal VM.
   *
   * @return \Robo\Result
   *   The command result.
   */
  protected function executeDoctorInsideVm() {
    $drupal_vm_config = Yaml::parse(file_get_contents($this->getConfigValue('repo.root') . '/box/config.yml'));
    $repo_root = $drupal_vm_config['vagrant_synced_folders'][0]['destination'];
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
      ->printMetadata(FALSE)
      ->run();

    return $result;
  }

}
