<?php

namespace Acquia\Blt\Robo\Command;
use Symfony\Component\Yaml\Yaml;

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFile extends \Robo\Tasks
{

  protected $config = [];
  protected $repoRoot;
  protected $bltRoot;
  protected $docroot;
  protected $bin;

  public function __construct() {
    $this->setRepoRoot();
    $this->setBltRoot();
    $this->docroot = "{$this->repoRoot}/docroot";
    $this->setConfig();
    $this->bin = "{$this->repoRoot}/vendor/bin";
  }

  public function setRepoRoot() {
    $possible_repo_roots = [
      $_SERVER['PWD'],
      getcwd(),
    ];

    foreach ($possible_repo_roots as $possible_repo_root) {
      if (file_exists("$possible_repo_root/blt/project.yml")) {
        $this->repoRoot = $possible_repo_root;
        break;
      }
    }
  }

  public function setBltRoot() {
    $this->bltRoot = dirname(dirname(dirname(dirname(__FILE__))));
  }

  public function setConfig() {
    $default_config =  Yaml::parse(file_get_contents("{$this->bltRoot}/phing/build.yml"));
    $this->config = Yaml::parse(file_get_contents("{$this->repoRoot}/blt/project.yml"));
    $this->config = $this->array_merge_recursive_distinct($this->config, $default_config);

    array_walk_recursive($this->config, function (&$value, $key) {
      $value = str_replace('${repo.root}', $this->repoRoot, $value);
      $value = str_replace('${blt.root}', $this->bltRoot, $value);
      $value = str_replace('${docroot}', $this->docroot, $value);
    });
  }

  /**
   * Runs all tests, including Behat, PHPUnit, and Security Update check.
   *
   * @command tests
   */
  public function tests() {
    $this->testsAll();
  }

  /**
   * Runs all tests, including Behat, PHPUnit, and Security Update check.
   *
   * @command tests:all
   */
  public function testsAll() {
    $this->testsBehat();
    $this->testsPhpUnit();
  }

  /**
   * @command tests:behat
   */
  public function testsBehat() {
    $this->yell("Repo root is {$this->repoRoot}");
    $this->killByPort('4444');
    $this->killByName('selenium');
    $this->killByName('phantomjs');

    if ($this->config['behat']['launch-phantomjs']) {
      $this->launchPhantomJs();
    }

    $this->_mkdir("{$this->repoRoot}/{$this->config['reports']['localDir']})");

    foreach ($this->config['behat']['paths'] as $behat_path) {
      $this->_exec("{$this->bin}/behat $behat_path -c {$this->config['behat']['config']} -p {$this->config['behat']['profile']}");
    }
  }

  /**
   * @command tests:phpunit
   */
  public function testsPhpUnit() {

  }

  protected function launchPhantomJs() {
    $this->verifyPhantomJsConfig();

    $this->say("Launching PhantomJS GhostDriver.");
    $this->taskExec("{$this->bin}/phantomjs")
      ->option("webdriver", 4444)
      ->background()
      ->run();

    // Wait for http://127.0.0.1:4444/wd/hub to become available.
  }

  protected function verifyPhantomJsConfig() {
    $result = $this->_exec("grep 'jakoch/phantomjs-installer' composer.json");
    if (!$result->wasSuccessful()) {
      $this->yell("behat.launch-phantomjs is true, but jakoch/phantomjs-installer is not required in composer.json.");
      $answer = $this->confirm("Do you want to require jakoch/phantomjs-installer via Composer?");
      if ($answer == 'y') {
        $this->_exec("composer require jakoch/phantomjs-installer --dev");
      }
      else {
        throw new \Exception("Cannot launch PhantomJS it is not installed.");
      }
    }

    $result = $this->_exec("grep installPhantomJS composer.json");
    if (!$result->wasSuccessful()) {
      $this->yell("behat.launch-phantomjs is true, but the install-phantomjs script is not defined in composer.json.");
      $answer = $this->confirm("Do you want to add an 'install-phantomjs' script to your composer.json?");
      if ($answer == 'y') {
        $this->_exec("{$this->bin}/blt-console configure:phantomjs {$this->repoRoot}");
      }
      else {
        throw new \Exception("Cannot launch PhantomJS because the install-phantomjs script is not present in composer.json. Add it, or use Selenium instead.");
      }
    }

    if (!file_exists("{$this->bin}/phantomjs")) {
      $this->_exec("composer install-phantom");
    }
  }

  protected function killByPort($port) {
    $this->say("Killing all processes on port $port");
    $this->_exec("lsof -ti tcp:$port | xargs kill");
  }

  protected function killByName($name) {
    $this->say("Killing all processing containing string '$name'");
    $this->_exec("pgrep $name | xargs kill");
  }

  protected function killPhantomJs() {

  }

  protected function array_merge_recursive_distinct ( array &$array1, array &$array2 )
  {
    $merged = $array1;

    foreach ( $array2 as $key => &$value )
    {
      if ( is_array ( $value ) && isset ( $merged [$key] ) && is_array ( $merged [$key] ) )
      {
        $merged [$key] = $this->array_merge_recursive_distinct ( $merged [$key], $value );
      }
      else
      {
        $merged [$key] = $value;
      }
    }

    return $merged;
  }
}
