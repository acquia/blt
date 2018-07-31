<?php

namespace Acquia\Blt\Tests;

use Acquia\Blt\Robo\Blt;
use Acquia\Blt\Robo\Common\YamlMunge;
use Acquia\Blt\Robo\Config\ConfigInitializer;
use PHPUnit\Framework\TestCase;
use Robo\Robo;
use Symfony\Component\Console\Input\ArrayInput;
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
abstract class BltProjectTestBase extends TestCase {

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

  protected $site1Dir;
  protected $site2Dir;
  protected $sandboxInstanceClone;

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

    $ci_config = YamlMunge::mungeFiles($this->sandboxInstance . "/blt/ci.blt.yml", $this->bltDirectory . "/scripts/blt/ci/internal/ci.yml");
    YamlMunge::writeFile($this->sandboxInstance . "/blt/ci.blt.yml", $ci_config);

    // Config is overwritten for each $this->blt execution.
    $this->reInitializeConfig($this->createBltInput(NULL, []));
    $this->drush('cache-rebuild', NULL, FALSE);
    $this->dbDump = $this->sandboxInstance . "/bltDbDump.sql";

    // Multisite settings.
    $this->site1Dir = 'default';
    $this->site2Dir = 'site2';
    $this->sandboxInstanceClone = $this->sandboxInstance . "2";
  }

  /**
   * Outputs debugging message.
   *
   * @param $message
   */
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
    $this->blt('drupal:hash-salt:init');
    $this->blt('drupal:deployment-identifier:init');
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
    Robo::unsetContainer();

    if (getenv('BLT_PRINT_COMMAND_OUTPUT')) {
      $output->writeln("<comment>------End BLT output---------</comment>");
      $output->writeln("");
    }

    if ($status_code && $stop_on_error) {
      throw new \Exception("BLT command exited with non-zero exit code.");
    }

    return [$status_code, $output->fetch(), $config];
  }

  /**
   * @param $message
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   */
  protected function writeFullWidthLine($message, $output) {
    $terminal_width = (new Terminal())->getWidth();
    $padding_len = ($terminal_width - strlen($message)) / 2;
    $pad = $padding_len > 0 ? str_repeat('-', $padding_len) : '';
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

  /**
   * @param $site1_dir
   * @param $site2_dir
   * @param $test_project_dir
   * @param $test_project_clone_dir
   */
  protected function prepareMultisites($site1_dir, $site2_dir, $test_project_dir, $test_project_clone_dir) {
    // Set test project vars.
    $site1_local_uri = 'local.blted8.site1.com';
    $site2_local_uri = 'local.blted8.site2.com';
    $site1_local_db_name = 'drupal';
    $site2_local_db_name = 'drupal2';
    $site1_local_human_name = "Site 1 Local";
    $site2_local_human_name = "Site 2 Local";
    $site1_remote_drush_alias = "$site1_dir.clone";
    $site2_remote_drush_alias = "$site2_dir.clone";

    // Create test project clone vars.
    $site1_clone_uri = 'local.blted82.site1.com';
    $site2_clone_uri = 'local.blted82.site2.com';
    $site1_clone_db_name = 'drupal3';
    $site2_clone_db_name = 'drupal4';
    $site1_clone_human_name = "Site 1 Clone";
    $site2_clone_human_name = "Site 2 Clone";
    $this->blt("recipes:multisite:init", [
      '--site-dir' => $site2_dir,
      '--site-uri' => "http://" . $site2_local_uri,
      '--remote-alias' => $site2_remote_drush_alias,
      '--yes' => '',
    ]);

    $this->fs->remove($test_project_clone_dir);
    $this->fs->mirror($test_project_dir, $test_project_clone_dir);

    // Create drush alias for site1.
    $aliases['clone'] = [
      'root' => $test_project_clone_dir,
      'uri' => $site1_dir,
    ];
    YamlMunge::mergeArrayIntoFile($aliases,
      "$test_project_dir/drush/sites/$site1_dir.site.yml");

    // Create drush alias for site2.
    $aliases['clone'] = [
      'root' => $test_project_clone_dir,
      'uri' => $site2_dir,
    ];
    YamlMunge::mergeArrayIntoFile($aliases,
      "$test_project_dir/drush/sites/$site2_dir.site.yml");

    // Site 1 local.
    $project_yml = [];
    $project_yml['project']['local']['hostname'] = $site1_local_uri;
    $project_yml['project']['human_name'] = $site1_local_human_name;
    $project_yml['drupal']['db']['database'] = $site1_local_db_name;
    $project_yml['drush']['aliases']['remote'] = $site1_remote_drush_alias;
    YamlMunge::mergeArrayIntoFile($project_yml,
      $test_project_dir . "/docroot/sites/$site1_dir/blt.yml");

    // Site 2 local.
    $project_yml = [];
    $project_yml['project']['human_name'] = $site2_local_human_name;
    $project_yml['drupal']['db']['database'] = $site2_local_db_name;
    // drush.aliases.remote should already have been set via generate command.
    YamlMunge::mergeArrayIntoFile($project_yml,
      $test_project_dir . "/docroot/sites/$site2_dir/blt.yml");

    // Site 1 clone.
    $project_yml = [];
    $project_yml['project']['human_name'] = $site1_clone_human_name;
    $project_yml['drupal']['db']['database'] = $site1_clone_db_name;
    YamlMunge::mergeArrayIntoFile($project_yml,
      $test_project_clone_dir . "/docroot/sites/$site1_dir/blt.yml");

    // Site 2 clone.
    $project_yml = [];
    $project_yml['project']['human_name'] = $site2_clone_human_name;
    $project_yml['drupal']['db']['database'] = $site2_clone_db_name;
    YamlMunge::mergeArrayIntoFile($project_yml,
      $test_project_clone_dir . "/docroot/sites/$site2_dir/blt.yml");

    // Generate sites.php for local app.
    $sites[$site1_local_uri] = $site1_dir;
    $sites[$site2_local_uri] = $site2_dir;
    $contents = "<?php\n \$sites = " . var_export($sites, TRUE) . ";";
    file_put_contents($test_project_dir . "/docroot/sites/sites.php",
      $contents);

    // Generate sites.php for clone app.
    $sites[$site1_clone_uri] = $site1_dir;
    $sites[$site2_clone_uri] = $site2_dir;
    $contents = "<?php\n \$sites = " . var_export($sites, TRUE) . ";";
    file_put_contents($test_project_clone_dir . "/docroot/sites/sites.php",
      $contents);

    // Delete local.settings.php files so they can be regenerated with new
    // values in blt.yml files.
    $this->fs->remove([
      "$test_project_dir/docroot/sites/$site1_dir/settings/local.settings.php",
      "$test_project_dir/docroot/sites/$site2_dir/settings/local.settings.php",
      "$test_project_dir/docroot/sites/$site1_dir/local.drush.yml",
      "$test_project_dir/docroot/sites/$site2_dir/local.drush.yml",
      "$test_project_clone_dir/docroot/sites/$site1_dir/settings/local.settings.php",
      "$test_project_clone_dir/docroot/sites/$site2_dir/settings/local.settings.php",
      "$test_project_clone_dir/docroot/sites/$site1_dir/local.drush.yml",
      "$test_project_clone_dir/docroot/sites/$site2_dir/local.drush.yml",
    ]);

  }

}
