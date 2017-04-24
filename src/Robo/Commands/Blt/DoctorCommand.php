<?php

namespace Acquia\Blt\Robo\Commands\Blt;

use Acquia\Blt\Robo\BltTasks;
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

    if ($this->getInspector()->isDrupalVmLocallyInitialized() && $this->getInspector()->isDrupalVmBooted()) {
      $result = $this->executeDoctorInsideVm();
      if ($result->wasSuccessful()) {
        return $result;
      }
    }

    // Try BLT doctor with default alias. This might be a Drupal VM alias.
    $alias = $this->getConfigValue('drush.alias');
    $result = $this->executeDoctorOnHost($alias);

    // If default alias failed, try again using @self alias.
    if (!$result->wasSuccessful() && $alias != 'self') {
      $this->logger->warning("Unable to run the doctor using @$alias. Trying with @self...");
      $this->executeDoctorOnHost('self');
    }

    return $result;
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
   * Executes a command inside of Drupal VM.
   *
   * @param string $command
   *   The command to execute.
   *
   * @return \Robo\Result
   *   The command result.
   */
  protected function executeCommandInDrupalVm($command) {
    $result = $this->taskExec("vagrant exec '$command'")
      ->dir($this->getConfigValue('repo.root'))
      ->detectInteractive()
      ->run();

    return $result;
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
      ->run();

    return $result;
  }

}
