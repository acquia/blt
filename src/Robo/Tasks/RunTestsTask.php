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
   * @var string
   */
  protected $command;

  /**
   * @var string
   */
  protected $php;

  /**
   * @var string
   */
  protected $testEnvVars;

  /**
   * @var bool
   */
  protected $sudo;

  /**
   * @var string
   */
  protected $user;

  /**
   * The path the Drupal's run-tests.sh.
   *
   * @var string
   */
  protected $runTestsScriptCommand;

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
   * @return $this
   */
  public function testEnvVars($testEnvVars) {
    $this->testEnvVars = is_string($testEnvVars) ? $testEnvVars : NULL;
    return $this;
  }

  /**
   * @return $this
   */
  public function sudo(bool $sudo = TRUE) {
    $this->sudo = $sudo;
    return $this;
  }

  /**
   * @return $this
   */
  public function user($user) {
    $this->user = is_string($user) ? $user : NULL;
    return $this;
  }

  /**
   * @return $this
   */
  public function verbose() {
    $this->option("verbose");
    return $this;
  }

  /**
   * @return $this
   */
  public function all() {
    $this->option("all");
    return $this;
  }

  /**
   * @return $this
   */
  public function browser() {
    $this->option("browser");
    return $this;
  }

  /**
   * @return $this
   */
  public function clean() {
    $this->option("clean");
    return $this;
  }

  /**
   * @return $this
   */
  public function color() {
    $this->option("color");
    return $this;
  }

  /**
   * @return $this
   */
  public function dieOnFail() {
    $this->option("die-on-fail");
    return $this;
  }

  /**
   * @return $this
   */
  public function keepResultsTable() {
    $this->option("keep-results-table");
    return $this;
  }

  /**
   * @return $this
   */
  public function keepResults() {
    $this->option("keep-results");
    return $this;
  }

  /**
   * @return $this
   */
  public function listFilesJson() {
    $this->option("list-files-json");
    return $this;
  }

  /**
   * @return $this
   */
  public function nonHtml() {
    $this->option("non-html");
    return $this;
  }

  /**
   * @return $this
   */
  public function suppressDeprecations() {
    $this->option("suppress-deprecations");
    return $this;
  }

  /**
   * @param int $concurrency
   *
   * @return $this
   */
  public function concurrency($concurrency) {
    $this->option("concurrency", $concurrency);
    return $this;
  }

  /**
   * @param int $repeat
   *
   * @return $this
   */
  public function repeat($repeat) {
    $this->option("repeat", $repeat);
    return $this;
  }

  /**
   * @param string $dbUrl
   *
   * @return $this
   */
  public function dbUrl($dbUrl) {
    $this->option("dburl", $dbUrl);
    return $this;
  }

  /**
   * @param string $directory
   *
   * @return $this
   */
  public function directory($directory) {
    $this->option("directory", $directory);
    return $this;
  }

  /**
   * @param string $php
   *
   * @return $this
   */
  public function php($php) {
    $this->option("php", $php);
    return $this;
  }

  /**
   * @param string $sqlite
   *
   * @return $this
   */
  public function sqlite($sqlite) {
    $this->option("sqlite", $sqlite);
    return $this;
  }

  /**
   * @param string $url
   *
   * @return $this
   */
  public function url($url) {
    $this->option("url", $url);
    return $this;
  }

  /**
   * @param string $xml
   *
   * @return $this
   */
  public function xml($xml) {
    $this->option("xml", $xml);
    return $this;
  }

  /**
   * @param string $types
   *
   * @return $this
   */
  public function types($types) {
    $this->option('types', $types, ' ');
    return $this;
  }

  /**
   * @param array $tests
   *
   * @return $this
   */
  public function tests($tests) {
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
