<?php

namespace Acquia\Blt\Robo\Tasks;

use Robo\Common\ExecOneCommand;
use Robo\Contract\CommandInterface;
use Robo\Contract\PrintedInterface;
use Robo\Exception\TaskException;
use Robo\Task\BaseTask;

/**
 * Runs tests using Drupal's core/scripts/run-tests.sh.
 */
class RunTestsTask extends BaseTask implements CommandInterface, PrintedInterface {

  use ExecOneCommand;

  /**
   * Command.
   *
   * @var string
   */
  protected $command;

  /**
   * PHP.
   *
   * @var string
   */
  protected $php;

  /**
   * Test env vars.
   *
   * @var string
   */
  protected $testEnvVars;

  /**
   * Sudo.
   *
   * @var bool
   */
  protected $sudo;

  /**
   * User.
   *
   * @var string
   */
  protected $user;

  /**
   * The path the Drupal's run-tests.sh.
   *
   * @var string
   */
  protected $runTestsScriptCommand;

  /**
   * Constructor.
   */
  public function __construct($runTestsScriptCommand = NULL) {

    $this->runTestsScriptCommand = !is_null($runTestsScriptCommand) ? $runTestsScriptCommand : './core/scripts/run-tests.sh';
    $this->php = $this->findExecutable('php');

    if (!$this->command) {
      $this->command = $this->php;
      $this->php($this->php);
    }
    if (!$this->command) {
      throw new TaskException(__CLASS__, "PHP installation not found");
    }

  }

  /**
   * Test env vars.
   *
   * @return $this
   */
  public function testEnvVars($testEnvVars) {
    $this->testEnvVars = is_string($testEnvVars) ? $testEnvVars : NULL;
    return $this;
  }

  /**
   * Sudo.
   *
   * @return $this
   */
  public function sudo(bool $sudo = TRUE) {
    $this->sudo = $sudo;
    return $this;
  }

  /**
   * User.
   *
   * @return $this
   */
  public function user($user) {
    $this->user = is_string($user) ? $user : NULL;
    return $this;
  }

  /**
   * Verbose.
   *
   * @return $this
   */
  public function verbose() {
    $this->option("verbose");
    return $this;
  }

  /**
   * All.
   *
   * @return $this
   */
  public function all() {
    $this->option("all");
    return $this;
  }

  /**
   * Browser.
   *
   * @return $this
   */
  public function browser() {
    $this->option("browser");
    return $this;
  }

  /**
   * Clean.
   *
   * @return $this
   */
  public function clean() {
    $this->option("clean");
    return $this;
  }

  /**
   * Color.
   *
   * @return $this
   */
  public function color() {
    $this->option("color");
    return $this;
  }

  /**
   * Die on fail.
   *
   * @return $this
   */
  public function dieOnFail() {
    $this->option("die-on-fail");
    return $this;
  }

  /**
   * Keep results.
   *
   * @return $this
   */
  public function keepResultsTable() {
    $this->option("keep-results-table");
    return $this;
  }

  /**
   * Keep results.
   *
   * @return $this
   */
  public function keepResults() {
    $this->option("keep-results");
    return $this;
  }

  /**
   * List files.
   *
   * @return $this
   */
  public function listFilesJson() {
    $this->option("list-files-json");
    return $this;
  }

  /**
   * Non html.
   *
   * @return $this
   */
  public function nonHtml() {
    $this->option("non-html");
    return $this;
  }

  /**
   * Suppress deprecations.
   *
   * @return $this
   */
  public function suppressDeprecations() {
    $this->option("suppress-deprecations");
    return $this;
  }

  /**
   * Concurrency.
   *
   * @param int $concurrency
   *   Concurrency.
   *
   * @return $this
   */
  public function concurrency($concurrency) {
    $this->option("concurrency", $concurrency);
    return $this;
  }

  /**
   * Repeat.
   *
   * @param int $repeat
   *   Repeat.
   *
   * @return $this
   */
  public function repeat($repeat) {
    $this->option("repeat", $repeat);
    return $this;
  }

  /**
   * DB url.
   *
   * @param string $dbUrl
   *   Db url.
   *
   * @return $this
   */
  public function dbUrl($dbUrl) {
    $this->option("dburl", $dbUrl);
    return $this;
  }

  /**
   * Directory.
   *
   * @param string $directory
   *   Directory.
   *
   * @return $this
   */
  public function directory($directory) {
    $this->option("directory", $directory);
    return $this;
  }

  /**
   * PHP.
   *
   * @param string $php
   *   PHP.
   *
   * @return $this
   */
  public function php($php) {
    $this->option("php", $php);
    return $this;
  }

  /**
   * SQLlite.
   *
   * @param string $sqlite
   *   Sqllite.
   *
   * @return $this
   */
  public function sqlite($sqlite) {
    $this->option("sqlite", $sqlite);
    return $this;
  }

  /**
   * Url.
   *
   * @param string $url
   *   Url.
   *
   * @return $this
   */
  public function url($url) {
    $this->option("url", $url);
    return $this;
  }

  /**
   * Xml.
   *
   * @param string $xml
   *   Xml.
   *
   * @return $this
   */
  public function xml($xml) {
    $this->option("xml", $xml);
    return $this;
  }

  /**
   * Types.
   *
   * @param string $types
   *   Types.
   *
   * @return $this
   */
  public function types($types) {
    $this->option('types', $types, ' ');
    return $this;
  }

  /**
   * Tests.
   *
   * @param array $tests
   *   Tests.
   *
   * @return $this
   */
  public function tests(array $tests) {
    $this->args($tests);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCommand() {
    $env = isset($this->testEnvVars) ? "$this->testEnvVars " : "";
    $command = $this->command . ' ' . $this->runTestsScriptCommand . $this->arguments;
    $sudo = isset($this->user) && $this->sudo ? "sudo -u $this->user " : "";
    return $sudo ? $env . $sudo . $command : $env . $command;
  }

  /**
   * {@inheritdoc}
   */
  public function run() {
    $this->printTaskInfo('Running run-tests.sh {arguments}', ['arguments' => $this->arguments]);
    return $this->executeCommand($this->getCommand());
  }

}
