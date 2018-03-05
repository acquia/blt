<?php

namespace Acquia\Blt\Robo\Commands\Doctor;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;

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
   * @executeInVm
   */
  public function doctor() {
    $this->doctorCheck();
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
