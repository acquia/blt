<?php

namespace Acquia\Blt\Tests;

use Acquia\Blt\Robo\Exceptions\BltException;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Manage BLT testing sandbox.
 */
class SandboxManager {

  /**
   * @var \Symfony\Component\Filesystem\Filesystem
   */
  protected $fs;

  /**
   * BLT dir.
   *
   * @var bool|string
   */
  protected $bltDir;

  /**
   * Sandbox master.
   *
   * @var string
   */
  protected $sandboxMaster;

  /**
   * Sandbox instance.
   *
   * @var string
   */
  protected $sandboxInstance;

  /**
   * @var \Symfony\Component\Console\Output\ConsoleOutput
   */
  protected $output;

  /**
   * Temp.
   *
   * @var string
   */
  protected $tmp;

  /**
   * SandboxManager constructor.
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  public function __construct() {
    if (!getenv('ORCA_FIXTURE_DIR')) {
      throw new BltException('ORCA_FIXTURE_DIR must be set in order to run tests');
    }
    $this->output = new ConsoleOutput();
    $this->fs = new Filesystem();
    $this->tmp = sys_get_temp_dir();
    $this->sandboxInstance = $this->tmp . "/blt-sandbox-instance";
    $this->bltDir = realpath(dirname(__FILE__) . '/../../../');
    $this->sandboxMaster = getenv('ORCA_FIXTURE_DIR');
    // ORCA uses a relative symlink for BLT. This breaks in the sandbox
    // instance. Not fun!
    // @see https://github.com/composer/composer/issues/8700
    $this->fs->symlink($this->bltDir, $this->sandboxMaster . '/vendor/acquia/blt');
  }

  /**
   * Removes an existing sandbox instance.
   */
  public function removeSandboxInstance() {
    if (file_exists($this->sandboxInstance)) {
      $this->debug("Removing sandbox instance...");
      $this->makeSandboxInstanceWritable();
      $this->fs->remove($this->sandboxInstance);
    }
  }

  /**
   * Outputs debugging message.
   *
   * @param string $message
   *   Message.
   */
  public function debug($message) {
    if (getenv('BLT_PRINT_COMMAND_OUTPUT')) {
      $this->output->writeln($message);
    }
  }

  /**
   * Makes sandbox instance writable.
   */
  public function makeSandboxInstanceWritable() {
    $sites_dir = $this->sandboxInstance . "/docroot/sites";
    if (file_exists($sites_dir)) {
      $this->fs->chmod($sites_dir, 0755, 0000, TRUE);
    }
  }

  /**
   * Copies all files and dirs from master sandbox to instance.
   *
   * Will not overwrite existing files!
   *
   * @param array $options
   *   Options.
   */
  protected function copySandboxMasterToInstance(array $options = [
    'delete' => TRUE,
    'override' => FALSE,
  ]) {
    $this->debug("Copying sandbox master to sandbox instance...");
    $this->fs->mirror($this->sandboxMaster, $this->sandboxInstance, NULL,
      $options);
  }

  /**
   * Overwrites all files in sandbox instance.
   */
  public function replaceSandboxInstance() {
    $this->removeSandboxInstance();
    $this->copySandboxMasterToInstance();
  }

  /**
   * Get sandbox instance.
   *
   * @return mixed
   *   Mixed.
   */
  public function getSandboxInstance() {
    return $this->sandboxInstance;
  }

}
