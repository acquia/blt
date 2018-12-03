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

  public function setUp() {
    parent::setUp();
    $this->installDrupalMinimal();
    $this->docroot = $this->config->get("docroot");
    $this->reporoot = $this->config->get("repo.root");
    $this->sqlite = $this->config->get("tests.drupal.sqlite");
  }

  /**
   * Test Drupal's Simpletest type with run-tests.sh.
   */
  public function testDrupalRunTestsSimpletestTests() {
    list($status_code, $output, $config) = $this->blt("tests:drupal:run", [
      "--define" => [
        "tests.drupal.test-runner=run-tests",
        "tests.run-tests.color=true",
        "tests.run-tests.concurrency=1",
        "tests.run-tests.repeat=1",
        "tests.run-tests.0.tests.0=user",
        "tests.run-tests.0.types.0=Simpletest",
        "tests.run-tests.sqlite=$this->sqlite",
      ],
    ]);
    $results = file_get_contents($this->sandboxInstance . '/reports/phpunit/Drupal_user_Tests_RestRegisterUserTest.xml');
    $this->assertNotContains('failure type="failure"', $results);
  }

  /**
   * Test Drupal's PHPUnit-Unit type with run-tests.sh.
   */
  public function testDrupalRunTestsUnitTests() {
    list($status_code, $output, $config) = $this->blt("tests:drupal:run", [
      "--define" => [
        "tests.drupal.test-runner=run-tests",
        "tests.run-tests.color=true",
        "tests.run-tests.concurrency=2",
        "tests.run-tests.repeat=1",
        "tests.run-tests.0.tests.0=action",
        "tests.run-tests.0.types.0=PHPUnit-Unit",
        "tests.run-tests.sqlite=$this->sqlite",
      ],
    ]);
    $results = file_get_contents($this->sandboxInstance . '/reports/phpunit/Drupal_Tests_action_Unit_Menu_ActionLocalTasksTest.xml');
    $this->assertNotContains('failure type="failure"', $results);
  }

  /**
   * Test Drupal's PHPUnit-Kernel type with run-tests.sh.
   */
  public function testDrupalRunTestsKernelTests() {
    list($status_code, $output, $config) = $this->blt("tests:drupal:run", [
      "--define" => [
        "tests.drupal.test-runner=run-tests",
        "tests.run-tests.color=true",
        "tests.run-tests.concurrency=2",
        "tests.run-tests.repeat=1",
        "tests.run-tests.0.tests.0=action",
        "tests.run-tests.0.types.0=PHPUnit-Kernel",
        "tests.run-tests.sqlite=$this->sqlite",
      ],
    ]);
    $results = file_get_contents($this->sandboxInstance . '/reports/phpunit/Drupal_Tests_action_Kernel_Migrate_d7_MigrateActionsTest.xml');
    $this->assertNotContains('failure type="failure"', $results);
    $results = file_get_contents($this->sandboxInstance . '/reports/phpunit/Drupal_Tests_action_Kernel_Plugin_Action_EmailActionTest.xml');
    $this->assertNotContains('failure type="failure"', $results);
    $results = file_get_contents($this->sandboxInstance . '/reports/phpunit/Drupal_Tests_action_Kernel_Plugin_migrate_source_ActionTest.xml');
    $this->assertNotContains('failure type="failure"', $results);
  }

  /**
   * Test Drupal's PHPUnit-Functional type with run-tests.sh.
   */
  public function testDrupalRunTestsFunctionalTests() {
    list($status_code, $output, $config) = $this->blt("tests:drupal:run", [
      "--define" => [
        "tests.drupal.test-runner=run-tests",
        "tests.run-tests.color=true",
        "tests.run-tests.concurrency=2",
        "tests.run-tests.repeat=1",
        "tests.run-tests.0.tests.0=action",
        "tests.run-tests.0.types.0=PHPUnit-Functional",
        "tests.run-tests.sqlite=$this->sqlite",
      ],
    ]);
    $results = file_get_contents($this->sandboxInstance . '/reports/phpunit/Drupal_Tests_action_Functional_ActionListTest.xml');
    $this->assertNotContains('failure type="failure"', $results);
    $results = file_get_contents($this->sandboxInstance . '/reports/phpunit/Drupal_Tests_action_Functional_ActionUninstallTest.xml');
    $this->assertNotContains('failure type="failure"', $results);
    $results = file_get_contents($this->sandboxInstance . '/reports/phpunit/Drupal_Tests_action_Functional_BulkFormTest.xml');
    $this->assertNotContains('failure type="failure"', $results);
    $results = file_get_contents($this->sandboxInstance . '/reports/phpunit/Drupal_Tests_action_Functional_ConfigurationTest.xml');
    $this->assertNotContains('failure type="failure"', $results);
  }

  /**
   * Test Drupal's PHPUnit-FunctionalJavascript type with run-tests.sh.
   */
  public function testDrupalRunTestsFunctionalJavascriptTests() {
    list($status_code, $output, $config) = $this->blt("tests:drupal:run", [
      "--define" => [
        "tests.drupal.test-runner=run-tests",
        "tests.run-tests.color=true",
        "tests.run-tests.concurrency=1",
        "tests.run-tests.repeat=1",
        "tests.run-tests.0.tests.0=action",
        "tests.run-tests.0.types.0=PHPUnit-FunctionalJavascript",
        "tests.run-tests.sqlite=$this->sqlite",
      ],
    ]);
    $results = file_get_contents($this->sandboxInstance . '/reports/phpunit/Drupal_Tests_action_FunctionalJavascript_ActionFormAjaxTest.xml');
    $this->assertNotContains('failure type="failure"', $results);
  }

  /**
   * Test Drupal's unit testsuite with PHPUnit.
   */
  public function testDrupalPhpUnitUnitTests() {
    list($status_code, $output, $config) = $this->blt("tests:drupal:run", [
      "--define" => [
        "tests.drupal.test-runner=phpunit",
        "tests.phpunit.0.config=$this->docroot/core/phpunit.xml.dist",
        "tests.phpunit.0.path=$this->reporoot/core",
        "tests.phpunit.0.group=action",
        "tests.phpunit.0.testsuites.0=unit",
      ],
    ]);
    $results = file_get_contents($this->sandboxInstance . '/reports/phpunit/results.xml');
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
        "tests.phpunit.0.config=$this->docroot/core/phpunit.xml.dist",
        "tests.phpunit.0.path=$this->reporoot/core",
        "tests.phpunit.0.group=action",
        "tests.phpunit.0.testsuites.0=kernel",
      ],
    ]);
    $results = file_get_contents($this->sandboxInstance . '/reports/phpunit/results.xml');
    $this->assertContains('testsuite name="kernel"', $results);
    $this->assertContains('errors="0" failures="0" skipped="0"', $results);
  }

  /**
   * Test Drupal's functional testsuite type with PHPUnit.
   */
  public function testDrupalPhpUnitFunctionalTests() {
    list($status_code, $output, $config) = $this->blt("tests:drupal:run", [
      "--define" => [
        "tests.drupal.test-runner=phpunit",
        "tests.phpunit.0.config=$this->docroot/core/phpunit.xml.dist",
        "tests.phpunit.0.path=$this->reporoot/core",
        "tests.phpunit.0.group=action",
        "tests.phpunit.0.testsuites.0=functional",
      ],
    ]);
    $results = file_get_contents($this->sandboxInstance . '/reports/phpunit/results.xml');
    $this->assertContains('testsuite name="functional"', $results);
    $this->assertContains('errors="0" failures="0" skipped="0"', $results);
  }

  /**
   * Test Drupal's functional-jascript testsuite type with PHPUnit.
   */
  public function testDrupalPhpUnitFunctionalJavascriptTests() {
    list($status_code, $output, $config) = $this->blt("tests:drupal:run", [
      "--define" => [
        "tests.drupal.test-runner=phpunit",
        "tests.phpunit.0.config=$this->docroot/core/phpunit.xml.dist",
        "tests.phpunit.0.path=$this->reporoot/core",
        "tests.phpunit.0.group=action",
        "tests.phpunit.0.testsuites.0=functional-javascript",
      ],
    ]);
    $results = file_get_contents($this->sandboxInstance . '/reports/phpunit/results.xml');
    $this->assertContains('testsuite name="functional-javascript"', $results);
    $this->assertContains('errors="0" failures="0" skipped="0"', $results);
  }

}