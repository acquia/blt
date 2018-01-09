<?php

namespace Acquia\Blt\Robo\Commands\Doctor;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;
use Symfony\Component\Yaml\Yaml;

/**
 * Defines doctor command.
 */
class DoctorCommand extends BltTasks {

  protected $outputTable;
  protected $passed;
  protected $problems = [];

  /**
   * Inspects your local blt configuration for possible issues.
   *
   * @command doctor
   *
   * @launchWebServer
   */
  public function doctor() {
    // Attempt to run BLT doctor inside of a VM.
    if ($this->getInspector()->isDrupalVmLocallyInitialized()
      && $this->getInspector()->isDrupalVmBooted()
      && !$this->getInspector()->isVmCli()) {
      $this->logger->debug("Executing doctor inside Drupal VM.");
      $result = $this->executeDoctorInsideVm();
      if ($result->wasSuccessful()) {
        return $result->getExitCode();
      }
    }

    $this->doctorCheck();
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
    $command = "cd $repo_root && ./vendor/bin/blt doctor";

    return $this->executeCommandInDrupalVm($command);
  }

  /**
   * @command doctor:check
   *
   * @hidden
   */
  public function doctorCheck() {
    $status = $this->getInspector()->getStatus();
    $this->printArrayAsTable($status);

    $checks = [
      AcsfCheck::class,
      BehatCheck::class,
      ComposerCheck::class,
      ConfigCheck::class,
      DbCheck::class,
      DevDesktopCheck::class,
      DrupalCheck::class,
      DrupalVmCheck::class,
      DrushCheck::class,
      FileSystemCheck::class,
      NodeCheck::class,
      PhpCheck::class,
      SettingsFilesCheck::class,
      SimpleSamlPhpCheck::class,
      WebUriCheck::class,
    ];

    $success = TRUE;
    foreach ($checks as $class) {
      /** @var \Acquia\Blt\Robo\Commands\Doctor\DoctorCheck $object */
      $object = new $class($this->getConfig(), $this->getInspector(), $this->getContainer()->get('executor'), $status);
      $object->performAllChecks();
      $this->problems = array_merge($this->problems, $object->getProblems());
      if ($object->wasErrorLogged()) {
        $success = FALSE;
      }
    }

    $this->printArrayAsTable($this->problems, ['Check', "Problem"]);
    if (!$success) {
      throw new BltException("BLT Doctor discovered one or more critical issues.");
    }
  }

}
