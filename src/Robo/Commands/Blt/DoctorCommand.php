<?php

namespace Acquia\Blt\Robo\Commands\Blt;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Doctor\AcsfCheck;
use Acquia\Blt\Robo\Doctor\ComposerCheck;
use Acquia\Blt\Robo\Doctor\ConfigCheck;
use Acquia\Blt\Robo\Doctor\DbCheck;
use Acquia\Blt\Robo\Doctor\DevDesktopCheck;
use Acquia\Blt\Robo\Doctor\DrupalCheck;
use Acquia\Blt\Robo\Doctor\DrushCheck;
use Acquia\Blt\Robo\Doctor\FileSystemCheck;
use Acquia\Blt\Robo\Doctor\NodeCheck;
use Acquia\Blt\Robo\Doctor\PhpCheck;
use Acquia\Blt\Robo\Doctor\SettingsFilesCheck;
use Acquia\Blt\Robo\Doctor\SimpleSamlPhpCheck;
use Acquia\Blt\Robo\Doctor\WebUriCheck;
use Acquia\Blt\Robo\Exceptions\BltException;

/**
 * Defines doctor command.
 */
class DoctorCommand extends BltTasks {

  /**
   * Output table.
   *
   * @var string
   */
  protected $outputTable;

  /**
   * Whether passed.
   *
   * @var bool
   */
  protected $passed;

  /**
   * List of problems.
   *
   * @var array
   */
  protected $problems = [];

  /**
   * Inspects your local blt configuration for possible issues.
   *
   * @command blt:doctor
   *
   * @aliases doctor
   *
   * @launchWebServer
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  public function doctor() {
    $this->doctorCheck();
  }

  /**
   * Run checks.
   *
   * @command doctor:check
   *
   * @hidden
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  public function doctorCheck() {
    $status = $this->getInspector()->getStatus();
    $this->printArrayAsTable($status);

    $checks = [
      AcsfCheck::class,
      ComposerCheck::class,
      ConfigCheck::class,
      DbCheck::class,
      DevDesktopCheck::class,
      DrupalCheck::class,
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
      /** @var \Acquia\Blt\Robo\Doctor\DoctorCheck $object */
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
