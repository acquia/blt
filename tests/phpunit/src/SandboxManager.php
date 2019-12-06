<?php

namespace Acquia\Blt\Tests;

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

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
   * BLT require dev package dir.
   *
   * @var string
   */
  protected $bltRequireDevPackageDir;

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
   */
  public function __construct() {
    $this->output = new ConsoleOutput();
    $this->fs = new Filesystem();
    $this->tmp = sys_get_temp_dir();
    $this->sandboxMaster = $this->tmp . "/blt-sandbox-master";
    $this->sandboxInstance = $this->tmp . "/blt-sandbox-instance";
    $this->bltRequireDevPackageDir = $this->tmp . '/blt-require-dev';
    $this->bltDir = realpath(dirname(__FILE__) . '/../../../');
  }

  /**
   * Ensures that sandbox master exists and is up to date.
   *
   * @throws \Exception
   */
  public function bootstrap() {
    $this->output->writeln("Bootstrapping BLT testing framework...");
    $recreate_master = getenv('BLT_RECREATE_SANDBOX_MASTER');
    if (!file_exists($this->sandboxMaster) || $recreate_master) {
      $this->output->writeln("<comment>To prevent recreation of sandbox master on each bootstrap, set BLT_RECREATE_SANDBOX_MASTER=0</comment>");
      $this->createSandboxMaster();
    }
    else {
      $this->output->writeln("<comment>Skipping master sandbox creation, BLT_RECREATE_SANDBOX_MASTER is disabled.");
    }
  }

  /**
   * Creates a new master sandbox.
   *
   * @throws \Exception
   */
  public function createSandboxMaster() {
    $this->output->writeln("Creating master sandbox in <comment>{$this->sandboxMaster}</comment>...");
    $this->fs->remove($this->sandboxMaster);

    // This essentially mirrors what composer create-project would do, i.e. git
    // clone and composer install, but with tweaks to use local packages.
    $this->fs->mirror($this->bltDir . '/subtree-splits/blt-project', $this->sandboxMaster);
    $this->createBltRequireDevPackage();
    $this->updateSandboxMasterBltRepoSymlink();
    $this->installSandboxMasterDependencies();
    $this->removeSandboxInstance();
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

  /**
   * Updates composer.json in sandbox master to reference BLT via symlink.
   */
  protected function updateSandboxMasterBltRepoSymlink() {
    $composer_json_path = $this->sandboxMaster . "/composer.json";
    $composer_json_contents = json_decode(file_get_contents($composer_json_path));
    $composer_json_contents->repositories->blt = (object) [
      'type' => 'path',
      'url' => $this->bltDir,
      'options' => [
        'symlink' => TRUE,
      ],
    ];
    $composer_json_contents->require->{'acquia/blt'} = '*@dev';
    $composer_json_contents->repositories->{'blt-require-dev'} = (object) [
      'type' => 'path',
      'url' => $this->bltRequireDevPackageDir,
      'options' => [
        'symlink' => TRUE,
      ],
    ];
    $composer_json_contents->{'require-dev'}->{'acquia/blt-require-dev'} = 'dev-master';
    $this->fs->dumpFile($composer_json_path,
      json_encode($composer_json_contents, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
  }

  /**
   * Installs composer dependencies in sandbox master dir.
   *
   * @throws \Exception
   */
  protected function installSandboxMasterDependencies() {
    $command = '';
    $drupal_core_version = getenv('DRUPAL_CORE_VERSION');
    if ($drupal_core_version && $drupal_core_version != 'default') {
      $command .= 'composer require "drupal/core:' . $drupal_core_version . '" --no-update --no-interaction && ';
    }
    $command .= 'composer install --prefer-dist --no-progress --no-suggest';

    $process = new Process($command, $this->sandboxMaster);
    $process->setTimeout(60 * 60);
    $process->run(function ($type, $buffer) {
      $this->output->write($buffer);
    });
    if (!$process->isSuccessful()) {
      throw new \Exception("Composer installation failed.");
    }
  }

  /**
   * Create temporary copy of blt-require-dev.
   *
   * This new dir will be used as the reference path for acquia/blt-require-dev
   * in local testing. It cannot be a subdir of blt because Composer cannot
   * reference a package nested within another package.
   */
  protected function createBltRequireDevPackage() {
    $this->fs->mirror($this->bltDir . '/subtree-splits/blt-require-dev',
      $this->bltRequireDevPackageDir);
  }

}
