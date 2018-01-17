<?php

namespace Acquia\Blt\Tests;

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 *
 */
class BltPhpunitBootstrapper {

  /** @var \Symfony\Component\Filesystem\Filesystem*/
  protected $fs;
  protected $bltDir;
  protected $sandboxMaster;
  protected $sandboxInstance;
  /** @var \Symfony\Component\Console\Output\ConsoleOutput*/
  protected $output;
  protected $tmp;

  public function __construct() {
    $this->output = new ConsoleOutput();
    $this->fs = new Filesystem();
    $this->tmp = sys_get_temp_dir();
    $this->sandboxMaster = $this->tmp . "/blt-sandbox-master";
    $this->sandboxInstance = $this->tmp . "/blt-sandbox-instance";
    $this->bltDir = realpath(dirname(__FILE__) . '/../../../');
  }

  public function bootstrap() {
    $this->output->writeln("Bootstrapping BLT testing framework...");
    $recreate_master = getenv('BLT_RECREATE_SANDBOX_MASTER');
    if ($recreate_master) {
      $this->output->writeln("<comment>To prevent recreation of sandbox master on each bootstrap, set BLT_RECREATE_SANDBOX_MASTER=0</comment>");
      $this->createSandboxMaster();
    }
    else {
      $this->output->writeln("<comment>Skipping master sandbox creation, BLT_RECREATE_SANDBOX_MASTER is disabled.");
    }
  }

  /**
   * Creates a new master sandbox.
   */
  public function createSandboxMaster() {
    $this->output->writeln("Creating master sandbox in <comment>{$this->sandboxMaster}</comment>...");
    $fixture = $this->bltDir . "/tests/phpunit/fixtures/sandbox";
    $this->fs->remove($this->sandboxMaster);
    $this->fs->remove($this->sandboxInstance);
    $this->fs->mirror($fixture, $this->sandboxMaster);
    $composer_json_path = $this->sandboxMaster . "/composer.json";
    $composer_json_contents = json_decode(file_get_contents($composer_json_path));
    $composer_json_contents->repositories->blt->url = $this->bltDir;
    $this->fs->dumpFile($composer_json_path, json_encode($composer_json_contents, JSON_PRETTY_PRINT));

    $process = new Process(
      'composer install --prefer-dist --no-progress --no-suggest && git init && git add -A && git commit -m "Initial commit."',
      $this->sandboxMaster
    );
    $process->setTimeout(60 * 60);
    $process->run(function ($type, $buffer) {
      $this->output->write($buffer);
    });
  }

  /**
   * Creates a new sandbox instance using master as a reference.
   *
   * This will not overwrite existing files. Will delete files in destination
   * that are not in source.
   */
  public function copySandboxMasterToInstance($options = [
    'delete' => TRUE,
    'override' => FALSE,
  ]) {
    try {
      $this->makeSandboxInstanceWritable();
      $this->fs->mirror($this->sandboxMaster, $this->sandboxInstance, NULL,
        $options);
      chdir($this->sandboxInstance);
    }
    catch (\Exception $e) {
      $this->replaceSandboxInstance();
    }
  }

  public function removeSandboxInstance() {
    $this->fs->remove($this->sandboxInstance);
  }

  /**
   * Overwrites all files in sandbox instance.
   */
  public function replaceSandboxInstance() {
    $this->makeSandboxInstanceWritable();
    $this->removeSandboxInstance();
    $this->copySandboxMasterToInstance();
  }

  /**
   * @return mixed
   */
  public function getSandboxInstance() {
    return $this->sandboxInstance;
  }

  public function makeSandboxInstanceWritable() {
    $sites_dir = $this->sandboxInstance . "/docroot/sites";
    if (file_exists($sites_dir)) {
      $this->fs->chmod($sites_dir, 0755, 0000, TRUE);
    }
  }

}
