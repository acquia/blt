<?php

namespace Acquia\Blt\Tests;

use Acquia\Blt\Robo\Blt;
use Acquia\Blt\Robo\Config\ConfigInitializer;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * Class BltProjectTestBase.
 *
 * Base class for all tests that are executed within a blt project.
 */
abstract class BltProjectTestBase extends \PHPUnit_Framework_TestCase {

  /**
   * @var string
   */
  protected $sandboxInstance;
  /**
   * @var \Acquia\Blt\Robo\Config\DefaultConfig
   */
  protected $config;
  /**
   * @var \Acquia\Blt\Tests\BltPhpunitBootstrapper
   */
  protected $bootstrapper;
  /**
   * @var \Symfony\Component\Filesystem\Filesystem
   */
  protected $fs;
  /**
   * @var string
   */
  protected $dbDump;

  protected $bltDirectory;

  public function setUp() {
    parent::setUp();
    $this->bltDirectory = realpath(dirname(__FILE__) . '/../../../');
    $this->fs = new Filesystem();
    $this->bootstrapper = new BltPhpunitBootstrapper();

    // We use tearDown methods to call overwriteSandboxInstance() after
    // tests that are known to pollute files. However, during local
    // debugging, the tearDown method may not have been reached. Enabling
    // BLT_OVERWRITE_SANDBOX_INSTANCE allows for a totally clean slate each
    // time regardless of whether the previous test run reached tearDown. We
    // do not do this by default because it slows testing significantly.
    if (getenv('BLT_OVERWRITE_SANDBOX_INSTANCE')) {
      $this->bootstrapper->replaceSandboxInstance();
    }
    else {
      $this->bootstrapper->copySandboxMasterToInstance();
    }

    $this->sandboxInstance = $this->bootstrapper->getSandboxInstance();
    $this->initializeConfig();
    $this->dbDump = $this->sandboxInstance . "/bltDbDump.sql";
  }

  /**
   * @param $command
   * @param null $root
   *
   * @return string
   */
  protected function drush($command, $root = NULL) {
    if (!$root) {
      $root = $this->config->get('docroot');
    }
    $drush_bin = $this->sandboxInstance . '/vendor/bin/drush';
    $command_string = "$drush_bin $command --root=$root --no-interaction --no-ansi";
    $process = $this->execute($command_string);
    $output = $process->getOutput();

    return $output;
  }

  /**
   * @param $command
   * @param null $root
   *
   * @return mixed
   */
  protected function drushJson($command, $root = NULL) {
    $output = $this->drush($command . " --format=json", $root);
    $array = json_decode($output, TRUE);

    return $array;
  }

  /**
   * Executes a BLT command.
   *
   * @param $command
   * @param array $args
   * @param null $cwd
   * @param bool $stop_on_error
   *
   * @return array
   * @throws \Exception
   */
  protected function blt($command, $args = [], $cwd = NULL, $stop_on_error = TRUE) {
    if ($cwd) {
      $initial_cwd = getcwd();
      chdir($cwd);
    }

    $args['command'] = $command;
    $input = new ArrayInput($args);
    $input->setInteractive(FALSE);

    if (getenv('BLT_PRINT_COMMAND_OUTPUT')) {
      // @todo This casting throws warning when an arg is an array.
      // $command_string = (string) $input;
      $output = new BufferedConsoleOutput();
      $output->writeln("");
      $output->writeln("Executing <comment>$command</comment>");
      $output->writeln("<comment>------Begin BLT output-------</comment>");
    }
    else {
      $output = new BufferedOutput();
    }

    // Execute command.
    $blt = new Blt($this->config, $input, $output);
    $status_code = (int) $blt->run($input, $output);

    if (getenv('BLT_PRINT_COMMAND_OUTPUT')) {
      $output->writeln("<comment>------End BLT output---------</comment>");
      $output->writeln("");
    }

    if ($cwd) {
      chdir($initial_cwd);
    }

    if ($status_code && $stop_on_error) {
      throw new \Exception("BLT command exited with non-zero exit code.");
    }

    return [$status_code, $output->fetch()];
  }

  /**
   * Installs the minimal profile and dumps it to sql file at $this->dbDump.
   */
  protected function createDatabaseDumpFixture() {
    $drush_bin = $this->sandboxInstance . '/vendor/bin/drush';
    $this->execute("$drush_bin sql-drop");
    $this->installDrupalMinimal();
    $this->drush("sql-dump --result-file={$this->dbDump}");
  }

  /**
   * @param null $root
   * @param string $uri
   */
  protected function importDbFromFixture($root = NULL, $uri = 'default') {
    if (!$root) {
      $root = $this->config->get('docroot');
    }
    if (!file_exists($this->dbDump)) {
      $this->createDatabaseDumpFixture();
    }

    $drush_bin = $this->sandboxInstance . '/vendor/bin/drush';
    $this->execute("$drush_bin sql-drop --root=$root --uri=$uri");
    $this->execute("$drush_bin sqlc --root=$root --uri=$uri < {$this->dbDump}");
  }

  /**
   * @param $command
   * @param null $cwd
   *
   * @return \Symfony\Component\Process\Process
   */
  protected function execute($command, $cwd = NULL) {
    if (!$cwd) {
      $cwd = $this->sandboxInstance;
    }

    $process = new Process($command, $cwd);
    $process->setTimeout(NULL);
    if (getenv('BLT_PRINT_COMMAND_OUTPUT')) {
      $output = new ConsoleOutput();
      $output->writeln("");
      $output->writeln("Executing <comment>$command</comment>...");
      $output->writeln("<comment>------Begin command output-------</comment>");
      $process->run(function ($type, $buffer) use ($output) {
        $output->write($buffer);
      });
    }
    else {
      $process->run();
    }
    if (getenv('BLT_PRINT_COMMAND_OUTPUT')) {
      $output->writeln("<comment>------End command output---------</comment>");
      $output->writeln("");
    }

    return $process;
  }

  /**
   *
   */
  protected function initializeConfig() {
    $config_initializer = new ConfigInitializer($this->sandboxInstance,
      new ArrayInput([]));
    $this->config = $config_initializer->initialize();
  }

  /**
   *
   */
  protected function installDrupalMinimal() {
    $this->blt('setup', [
      '--define' => [
        'project.profile.name=minimal',
      ],
    ]);
  }

}
