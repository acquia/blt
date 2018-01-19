<?php

namespace Acquia\Blt\Tests;

use Acquia\Blt\Robo\Blt;
use Acquia\Blt\Robo\Config\ConfigInitializer;
use Acquia\Blt\Robo\Commands\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Terminal;
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
  protected $config = NULL;
  /**
   * @var \Acquia\Blt\Tests\SandboxManager
   */
  protected $sandboxManager;
  /**
   * @var \Symfony\Component\Filesystem\Filesystem
   */
  protected $fs;
  /**
   * @var string
   *
   * This fixture is shared between tests, but regenerated each time PHPUnit
   * is bootstrapped. Setting BLT_RECREATE_SANDBOX_MASTER=0 will prevent this.
   */
  protected $dbDump;

  /**
   * @var \Symfony\Component\Console\Output\ConsoleOutput
   */
  protected $output;

  /** @var string*/
  protected $bltDirectory;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->output = new ConsoleOutput();
    $this->printTestName();
    $this->bltDirectory = realpath(dirname(__FILE__) . '/../../../');
    $this->fs = new Filesystem();
    $this->sandboxManager = new SandboxManager();

    // We use tearDown methods to call overwriteSandboxInstance() after
    // tests that are known to pollute files. However, during local
    // debugging, the tearDown method may not have been reached. Enabling
    // BLT_REPLACE_SANDBOX_INSTANCE allows for a totally clean slate each
    // time regardless of whether the previous test run reached tearDown. We
    // do not do this by default because it slows testing significantly.
    if (getenv('BLT_REPLACE_SANDBOX_INSTANCE')) {
      $this->sandboxManager->replaceSandboxInstance();
    }
    else {
      $this->sandboxManager->refreshSandboxInstance();
    }

    $this->sandboxInstance = $this->sandboxManager->getSandboxInstance();
    // Config is overwritten for each $this->blt execution.
    $this->reInitializeConfig($this->createBltInput(NULL, []));
    $this->drush('cache-rebuild', NULL, FALSE);
    $this->dbDump = $this->sandboxInstance . "/bltDbDump.sql";
  }

  public function debug($message) {
    if (getenv('BLT_PRINT_COMMAND_OUTPUT')) {
      $this->output->writeln($message);
    }
  }

  /**
   *
   */
  protected function reInitializeConfig($input) {
    unset($this->config);
    $config_initializer = new ConfigInitializer($this->sandboxInstance, $input);
    $this->config = $config_initializer->initialize();
  }

  protected function dropDatabase() {
    $drush_bin = $this->sandboxInstance . '/vendor/bin/drush';
    $this->execute("$drush_bin sql-drop", NULL, FALSE);
  }

  /**
   * @param $command
   * @param null $cwd
   * @param bool $stop_on_error
   *
   * @return \Symfony\Component\Process\Process
   * @throws \Exception
   */
  protected function execute($command, $cwd = NULL, $stop_on_error = TRUE) {
    if (!$cwd) {
      $cwd = $this->sandboxInstance;
    }

    $process = new Process($command, $cwd);
    $process->setTimeout(NULL);
    if (getenv('BLT_PRINT_COMMAND_OUTPUT')) {
      $output = new ConsoleOutput();
      $output->writeln("");
      $output->writeln("Executing <comment>$command</comment>...");
      if (!$stop_on_error) {
        $output->writeln("Command failure is permitted.");
      }
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

    if (!$process->isSuccessful() && $stop_on_error) {
      throw new \Exception("Command exited with non-zero exit code.");
    }

    return $process;
  }

  /**
   * @param $command
   * @param null $root
   *
   * @return string
   */
  protected function drush($command, $root = NULL, $stop_on_error = TRUE) {
    if (!$root) {
      $root = $this->config->get('docroot');
    }
    $drush_bin = $this->sandboxInstance . '/vendor/bin/drush';
    $command_string = "$drush_bin $command --root=$root --no-interaction --ansi";
    $process = $this->execute($command_string, $root, $stop_on_error);
    $output = $process->getOutput();

    return $output;
  }

  /**
   * @param $command
   * @param null $root
   *
   * @return mixed
   */
  protected function drushJson($command, $root = NULL, $stop_on_error = TRUE) {
    $output = $this->drush($command . " --format=json", $root, $stop_on_error);
    $array = json_decode($output, TRUE);

    return $array;
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
    $this->execute("$drush_bin sql-drop --root=$root --uri=$uri", NULL, FALSE);
    $this->blt('setup:hash-salt');
    $this->execute("$drush_bin sql-cli --root=$root --uri=$uri < {$this->dbDump}");
  }

  /**
   * Installs the minimal profile and dumps it to sql file at $this->dbDump.
   */
  protected function createDatabaseDumpFixture() {
    $this->dropDatabase();
    $this->installDrupalMinimal();
    $this->drush("sql-dump --result-file={$this->dbDump}");
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

  /**
   * Executes a BLT command.
   *
   * @param $command
   * @param array $args
   * @param bool $stop_on_error
   *
   * @return array
   * @throws \Exception
   * @internal param null $cwd
   */
  protected function blt($command, $args = [], $stop_on_error = TRUE) {
    chdir($this->sandboxInstance);
    $input = $this->createBltInput($command, $args);

    if (getenv('BLT_PRINT_COMMAND_OUTPUT')) {
      $command_string = (string) $input;
      $output = new BufferedConsoleOutput();
      $output->writeln("");
      $output->writeln("Executing <comment>blt $command_string</comment> in " . $this->sandboxInstance);
      $output->writeln("<comment>------Begin BLT output-------</comment>");
    }
    else {
      $output = new BufferedOutput();
    }

    $config_initializer = new ConfigInitializer($this->sandboxInstance, $input);
    $config = $config_initializer->initialize();

    // Execute command.
    $blt = new Blt($config, $input, $output);
    $status_code = (int) $blt->run($input, $output);

    if (getenv('BLT_PRINT_COMMAND_OUTPUT')) {
      $output->writeln("<comment>------End BLT output---------</comment>");
      $output->writeln("");
    }

    if ($status_code && $stop_on_error) {
      throw new \Exception("BLT command exited with non-zero exit code.");
    }

    return [$status_code, $output->fetch()];
  }

  /**
   * @param $message
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   */
  protected function writeFullWidthLine($message, $output) {
    $terminal_width = (new Terminal())->getWidth();
    $padding_len = ($terminal_width - strlen($message)) / 2;
    $pad = str_repeat('-', $padding_len);
    $output->writeln("<comment>{$pad}{$message}{$pad}</comment>");
  }

  /**
   * @param $command
   * @param $args
   *
   * @return \Symfony\Component\Console\Input\InputInterface
   */
  protected function createBltInput($command = '', $args = []) {
    $defaults = [
      '--environment' => getenv('BLT_ENV'),
      '-vvv' => '',
      '--no-interaction' => '',
    ];
    $args = array_merge($args, $defaults);
    $prepend = [
      'command' => $command,
    ];
    $args = $prepend + $args;
    $input = new ArrayInput($args);
    $input->setInteractive(FALSE);

    return $input;
  }

  protected function tearDown() {
    $this->dropDatabase();
    unset($this->config);
  }

  protected function printTestName() {
    if (getenv('BLT_PRINT_COMMAND_OUTPUT')) {
      $this->output->writeln("");
      $this->writeFullWidthLine(get_class($this) . "::" . $this->getName(),
        $this->output);
    }
  }

}
