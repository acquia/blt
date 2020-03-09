<?php

namespace Acquia\Blt\Tests\Robo;

use Acquia\Blt\Robo\Common\EnvironmentDetector;
use PHPUnit\Framework\TestCase;

/**
 * Tests the EnvironmentDetector class.
 */
class EnvironmentDetectorTest extends TestCase {

  /**
   * Tests EnvironmentDetector::isAhDevEnv().
   *
   * @param string $ah_site_env
   *   The name of the site environment.
   * @param string $expected_env
   *   Environment type.
   *
   * @dataProvider providerTestIsEnv
   */
  public function testIsAhDevEnv($ah_site_env, $expected_env) {
    putenv("AH_SITE_ENVIRONMENT=$ah_site_env");
    $this::assertEquals($expected_env === 'dev', EnvironmentDetector::isAhDevEnv());
  }

  /**
   * Tests EnvironmentDetector::isAhStageEnv().
   *
   * @param string $ah_site_env
   *   The name of the site environment.
   * @param string $expected_env
   *   Environment type.
   *
   * @dataProvider providerTestIsEnv
   */
  public function testIsAhStageEnv($ah_site_env, $expected_env) {
    putenv("AH_SITE_ENVIRONMENT=$ah_site_env");
    $this::assertEquals($expected_env === 'stage', EnvironmentDetector::isAhStageEnv());
  }

  /**
   * Tests EnvironmentDetector::isAhProdEnv().
   *
   * @param string $ah_site_env
   *   The name of the site environment.
   * @param string $expected_env
   *   Environment type.
   *
   * @dataProvider providerTestIsEnv
   */
  public function testIsAhProdEnv($ah_site_env, $expected_env) {
    putenv("AH_SITE_ENVIRONMENT=$ah_site_env");
    $this::assertEquals($expected_env === 'prod', EnvironmentDetector::isAhProdEnv());
  }

  /**
   * Tests EnvironmentDetector::isAhOdeEnv().
   *
   * @param string $ah_site_env
   *   The name of the site environment.
   * @param string $expected_env
   *   Environment type.
   *
   * @dataProvider providerTestIsEnv
   */
  public function testIsAhOdeEnv($ah_site_env, $expected_env) {
    putenv("AH_SITE_ENVIRONMENT=$ah_site_env");
    $this::assertEquals($expected_env === 'ode', EnvironmentDetector::isAhOdeEnv());
  }

  /**
   * Provides values to testIsAhEnv tests.
   *
   * @return array
   *   An array of values to test, environment name mapped to environment type.
   */
  public function providerTestIsEnv() {
    return [
      ['dev', 'dev'],
      ['dev1', 'dev'],
      ['01dev', 'dev'],
      ['02dev', 'dev'],
      ['test', 'stage'],
      ['stg', 'stage'],
      ['01test', 'stage'],
      ['02test', 'stage'],
      ['prod', 'prod'],
      ['01live', 'prod'],
      ['02live', 'prod'],
      ['ode1', 'ode'],
      ['ode2', 'ode'],
    ];
  }

}
