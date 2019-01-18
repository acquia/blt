<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class DrupalTest.
 */
class DrupalTest extends BltProjectTestBase {

  /**
   * @var string
   */
  protected $docroot;

  /**
   * @var string
   */
  protected $reporoot;

  /**
   * @var string
   */
  protected $sqlite;

  /**
   * @var string
   */
  protected $url;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->installDrupalMinimal();
    $this->docroot = $this->config->get("docroot");
    $this->reporoot = $this->config->get("repo.root");
    $this->sqlite = $this->config->get("tests.drupal.sqlite");
    $this->url = $this->config->get("tests.drupal.simpletest-base-url");
  }

  /**
   * Test Drupal's Simpletest type with run-tests.sh.
   */
  public function testDrupalRunTestsSimpletestTests() {
    list($status_code, $output, $config) = $this->blt("tests:drupal:run", [
      "--define" => [
        "tests.drupal.test-runner=run-tests-script",
        "tests.drupal.drupal-tests.0.color=true",
        "tests.drupal.drupal-tests.0.concurrency=1",
        "tests.drupal.drupal-tests.0.repeat=1",
        "tests.drupal.drupal-tests.0.tests.0=user",
        "tests.drupal.drupal-tests.0.types.0=Simpletest",
        "tests.drupal.drupal-tests.0.sqlite=$this->sqlite",
        "tests.drupal.drupal-tests.0.url=$this->url",
      ],
    ]);
    $results = file_get_contents($this->sandboxInstance . '/reports/drupal/run-tests-script/Drupal_user_Tests_RestRegisterUserTest.xml');
    $this->assertNotContains('failure type="failure"', $results);
  }

  /**
   * Test Drupal's PHPUnit-Unit type with run-tests.sh.
   */
  public function testDrupalRunTestsUnitTests() {
    list($status_code, $output, $config) = $this->blt("tests:drupal:run", [
      "--define" => [
        "tests.drupal.test-runner=run-tests-script",
        "tests.drupal.drupal-tests.0.color=true",
        "tests.drupal.drupal-tests.0.concurrency=2",
        "tests.drupal.drupal-tests.0.repeat=1",
        "tests.drupal.drupal-tests.0.tests.0=action",
        "tests.drupal.drupal-tests.0.types.0=PHPUnit-Unit",
        "tests.drupal.drupal-tests.0.sqlite=$this->sqlite",
        "tests.drupal.drupal-tests.0.url=$this->url",
      ],
    ]);
    $results = file_get_contents($this->sandboxInstance . '/reports/drupal/run-tests-script/Drupal_Tests_action_Unit_Menu_ActionLocalTasksTest.xml');
    $this->assertNotContains('failure type="failure"', $results);
  }

  /**
   * Test Drupal's PHPUnit-Kernel type with run-tests.sh.
   */
  public function testDrupalRunTestsKernelTests() {
    list($status_code, $output, $config) = $this->blt("tests:drupal:run", [
      "--define" => [
        "tests.drupal.test-runner=run-tests-script",
        "tests.drupal.drupal-tests.0.color=true",
        "tests.drupal.drupal-tests.0.concurrency=2",
        "tests.drupal.drupal-tests.0.repeat=1",
        "tests.drupal.drupal-tests.0.tests.0=action",
        "tests.drupal.drupal-tests.0.types.0=PHPUnit-Kernel",
        "tests.drupal.drupal-tests.0.sqlite=$this->sqlite",
        "tests.drupal.drupal-tests.0.url=$this->url",
      ],
    ]);
    $results = file_get_contents($this->sandboxInstance . '/reports/drupal/run-tests-script/Drupal_Tests_action_Kernel_Migrate_d7_MigrateActionsTest.xml');
    $this->assertNotContains('failure type="failure"', $results);
    $results = file_get_contents($this->sandboxInstance . '/reports/drupal/run-tests-script/Drupal_Tests_action_Kernel_Plugin_Action_EmailActionTest.xml');
    $this->assertNotContains('failure type="failure"', $results);
    $results = file_get_contents($this->sandboxInstance . '/reports/drupal/run-tests-script/Drupal_Tests_action_Kernel_Plugin_migrate_source_ActionTest.xml');
    $this->assertNotContains('failure type="failure"', $results);
  }

  /**
   * Test Drupal's PHPUnit-Functional type with run-tests.sh.
   */
  public function testDrupalRunTestsFunctionalTests() {}

  /**
   * Test Drupal's PHPUnit-FunctionalJavascript type with run-tests.sh.
   */
  public function testDrupalRunTestsFunctionalJavascriptTests() {
    list($status_code, $output, $config) = $this->blt("tests:drupal:run", [
      "--define" => [
        "tests.drupal.test-runner=run-tests-script",
        "tests.drupal.drupal-tests.0.color=true",
        "tests.drupal.drupal-tests.0.concurrency=1",
        "tests.drupal.drupal-tests.0.repeat=1",
        "tests.drupal.drupal-tests.0.tests.0=action",
        "tests.drupal.drupal-tests.0.types.0=PHPUnit-FunctionalJavascript",
        "tests.drupal.drupal-tests.0.sqlite=$this->sqlite",
        "tests.drupal.drupal-tests.0.url=$this->url",
      ],
    ]);
    $results = file_get_contents($this->sandboxInstance . '/reports/drupal/run-tests-script/Drupal_Tests_action_FunctionalJavascript_ActionFormAjaxTest.xml');
    $this->assertNotContains('failure type="failure"', $results);
  }

  /**
   * Test Drupal's unit testsuite with PHPUnit.
   */
  public function testDrupalPhpUnitUnitTests() {
    list($status_code, $output, $config) = $this->blt("tests:drupal:run", [
      "--define" => [
        "tests.drupal.test-runner=phpunit",
        "tests.drupal.phpunit.0.config=$this->docroot/core/phpunit.xml.dist",
        "tests.drupal.phpunit.0.path=$this->reporoot/core",
        "tests.drupal.phpunit.0.group=action",
        "tests.drupal.phpunit.0.testsuites.0=unit",
      ],
    ]);
    $results = file_get_contents($this->sandboxInstance . '/reports/drupal/phpunit/results.xml');
    $this->assertContains('testsuite name="unit"', $results);
    $this->assertContains('errors="0" failures="0" skipped="0"', $results);
  }

  /**
   * Test Drupal's kernel testsuite type with PHPUnit.
   */
  public function testDrupalPhpUnitKernelTests() {
    list($status_code, $output, $config) = $this->blt("tests:drupal:run", [
      "--define" => [
        "tests.drupal.test-runner=phpunit",
        "tests.drupal.phpunit.0.config=$this->docroot/core/phpunit.xml.dist",
        "tests.drupal.phpunit.0.path=$this->reporoot/core",
        "tests.drupal.phpunit.0.group=action",
        "tests.drupal.phpunit.0.testsuites.0=kernel",
      ],
    ]);
    $results = file_get_contents($this->sandboxInstance . '/reports/drupal/phpunit/results.xml');
    $this->assertContains('testsuite name="kernel"', $results);
    $this->assertContains('errors="0" failures="0" skipped="0"', $results);
  }

  /**
   * Test Drupal's functional testsuite type with PHPUnit.
   */
  public function testDrupalPhpUnitFunctionalTests() {}

  /**
   * Test Drupal's functional-jascript testsuite type with PHPUnit.
   */
  public function testDrupalPhpUnitFunctionalJavascriptTests() {
    list($status_code, $output, $config) = $this->blt("tests:drupal:run", [
      "--define" => [
        "tests.drupal.test-runner=phpunit",
        "tests.drupal.phpunit.0.config=$this->docroot/core/phpunit.xml.dist",
        "tests.drupal.phpunit.0.path=$this->reporoot/core",
        "tests.drupal.phpunit.0.group=action",
        "tests.drupal.phpunit.0.testsuites.0=functional-javascript",
      ],
    ]);
    $results = file_get_contents($this->sandboxInstance . '/reports/drupal//phpunit/results.xml');
    $this->assertContains('testsuite name="functional-javascript"', $results);
    $this->assertContains('errors="0" failures="0" skipped="0"', $results);
  }

}
