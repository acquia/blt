<?php

namespace Acquia\Blt\Tests;

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class BltPhpunitBootstrapper{

  /** @var Filesystem */
  protected $fs;
  protected $bltDir;
  protected $sandboxMaster;
  protected $sandboxInstance;
  /** @var ConsoleOutput */
  protected $output;
  protected $tmp;

  public function __construct() {
    $this->output = new ConsoleOutput();
    $this->fs = new Filesystem();
    $this->tmp = sys_get_temp_dir();
    $this->sandboxMaster = $this->tmp . "/blt-sandbox-master";
    $this->sandboxInstance = $this->tmp . "/sandbox-instance";
    $this->bltDir = realpath(dirname(__FILE__) . '/../../../');
  }

  public function bootstrap() {
    $this->output->writeln("Bootstrapping BLT testing framework...");
    $recreate_master = getenv('BLT_RECREATE_SANDBOX_MASTER');
    if ($recreate_master) {
      $this->createSandboxMaster();
    }
    else {
      $this->output->writeln("<comment>Skipping master sandbox creation, BLT_RECREATE_SANDBOX_MASTER is disabled.");
    }
  }

  public function createSandboxMaster() {
    $this->output->writeln("Creating master sandbox in <comment>{$this->sandboxMaster}</comment>...");
    $fixture = $this->bltDir . "/tests/phpunit/fixtures/sandbox";
    $this->fs->remove([$this->sandboxMaster]);
    $this->fs->mkdir([$this->sandboxMaster]);
    $this->fs->mirror($fixture, $this->sandboxMaster);
    $composer_json_path = $this->sandboxMaster . "/composer.json";
    $composer_json_contents = json_decode(file_get_contents($composer_json_path));
    $composer_json_contents->repositories->blt->url = $this->bltDir;
    $this->fs->dumpFile($composer_json_path, json_encode($composer_json_contents, JSON_PRETTY_PRINT));
    $process = new Process(
      'composer install --prefer-dist --no-progress --no-suggest' .
      ' && git init' .
      ' && git add -A' .
      ' && git commit -m "Initial commit."',
      $this->sandboxMaster
    );
    $process->setTimeout(60 * 60);
    $process->run(function ($type, $buffer) {
      $this->output->write($buffer);
    });
  }

  public function createSandboxInstance() {
    $this->output->writeln("Creating sandbox instance in <comment>{$this->sandboxInstance}</comment>...");
    $this->fs->mirror($this->sandboxMaster, $this->sandboxInstance);
    chdir($this->sandboxInstance);
  }

  /**
   * @return mixed
   */
  public function getSandboxInstance() {
    return $this->sandboxInstance;
  }

}
