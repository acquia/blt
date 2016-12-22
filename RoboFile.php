<?php
/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFile extends \Robo\Tasks
{

  protected $config = [];
  protected $repoRoot;
  protected $bin;

  public function __construct() {
    $this->repoRoot = getcwd();
    $this->bin = "{$this->repoRoot}/vendor/bin";

    // @todo remove this. testing.
    $this->config = [
      'launch-phantomjs' => true,
    ];
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
    $this->killByPort('4444');
    $this->killByName('selenium');
    $this->killByName('phantomjs');

    if ($this->config['launch-phantomjs']) {
      $this->launchPhantomJs();
    }
  }

  /**
   * @command tests:phpunit
   */
  public function testsPhpUnit() {

  }

  protected function launchPhantomJs() {
    $this->verifyPhantomJsConfig();
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
        throw new Exception("Cannot launch PhantomJS it is not installed.");
      }
    }

    $result = $this->_exec("grep installPhantomJS composer.json");
    if (!$result->wasSuccessful()) {
      $this->yell("behat.launch-phantomjs is true, but the install-phantomjs script is not defined in composer.json.");
      $answer = $this->confirm("Do you want to add an 'install-phantomjs' script to your composer.json?");
      if ($answer == 'y') {
        $this->_exec("$bin/blt-console configure:phantomjs {$this->repoRoot}");
      }
      else {
        throw new Exception("Cannot launch PhantomJS because the install-phantomjs script is not present in composer.json. Add it, or use Selenium instead.");
      }
    }

    if (!file_exists("$bin/phantomjs")) {
      $this->_exec("composer install-phantom");
      $this->say("Launching PhantomJS GhostDriver.");
      $this->taskExecStack("phantomjs")
        ->option("webdriver", 4444)
        ->background()
        ->run();
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
}
