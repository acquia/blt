<?php

namespace Acquia\Blt\Robo\Common;

use Acquia\Blt\Robo\Exceptions\BltException;

/**
 * Class EnvironmentDetector.
 *
 * @package Acquia\Blt\Robo\Common
 *
 * Attempts to detect various properties about the current hosting environment.
 */
class EnvironmentDetector {

  /**
   * Is AH env.
   */
  public static function isAhEnv() {
    return (bool) self::getAhEnv();
  }

  /**
   * Is ACSF env.
   *
   * @param mixed $ah_group
   *   The Acquia Hosting site / group name (e.g. my_subscription).
   * @param mixed $ah_env
   *   The Acquia Hosting environment name (e.g. 01dev).
   *
   * @return bool
   *   Bool.
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  public static function isAcsfEnv($ah_group = NULL, $ah_env = NULL) {
    if (is_null($ah_group)) {
      $ah_group = self::getAhGroup();
    }

    if (is_null($ah_env)) {
      $ah_env = self::getAhEnv();
    }

    if (empty($ah_group) || empty($ah_env)) {
      return FALSE;
    }

    $is_acsf_json = file_exists("/mnt/files/$ah_group.$ah_env/files-private/sites.json");
    $is_acsf_env_name = preg_match('/\d+(dev|test|live|update)(up)?/', $ah_env);

    if ($is_acsf_json != $is_acsf_env_name) {
      throw new BltException("Cannot determine if this is an ACSF environment or not.");
    }

    return ($is_acsf_env_name && $is_acsf_json);
  }

  /**
   * Is AH prod.
   */
  public static function isAhProdEnv() {
    $ah_env = self::getAhEnv();
    // ACE prod is 'prod'; ACSF can be '01live', '02live', ...
    return $ah_env == 'prod' || preg_match('/^\d+live$/', $ah_env);
  }

  /**
   * Is AH stage.
   */
  public static function isAhStageEnv() {
    $ah_env = self::getAhEnv();
    // ACE staging is 'test' or 'stg'; ACSF is '01test', '02test', ...
    return preg_match('/^\d+test$/', $ah_env) || $ah_env == 'stg';
  }

  /**
   * Is AH dev.
   */
  public static function isAhDevEnv() {
    // ACE dev is 'dev', 'dev1', ...; ACSF dev is '01dev', '02dev', ...
    return (preg_match('/^\d+dev\d+$/', self::getAhEnv()));
  }

  /**
   * Is AH ODE.
   */
  public static function isAhOdeEnv($ah_env = NULL) {
    if (is_null($ah_env)) {
      $ah_env = self::getAhEnv();
    }
    // CDEs (formerly 'ODEs') can be 'ode1', 'ode2', ...
    return (preg_match('/^ode\d+$/', $ah_env));
  }

  /**
   * Is AH devcloud.
   */
  public static function isAhDevCloud() {
    return (!empty($_SERVER['HTTP_HOST']) && strstr($_SERVER['HTTP_HOST'], 'devcloud'));
  }

  /**
   * Get AH group.
   */
  public static function getAhGroup() {
    return isset($_ENV['AH_SITE_GROUP']) ? $_ENV['AH_SITE_GROUP'] : NULL;
  }

  /**
   * Get AH env.
   */
  public static function getAhEnv() {
    return isset($_ENV['AH_SITE_ENVIRONMENT']) ? $_ENV['AH_SITE_ENVIRONMENT'] : NULL;
  }

  /**
   * Get CI.
   */
  public static function getCiEnv() {
    $mapping = [
      'TRAVIS' => 'travis',
      'PIPELINE_ENV' => 'pipelines',
      'PROBO_ENVIRONMENT' => 'probo',
      'TUGBOAT_URL' => 'tugboat',
      'GITLAB_CI' => 'gitlab',
    ];
    foreach ($mapping as $env_var => $ci_name) {
      if (isset($_ENV[$env_var])) {
        return $ci_name;
      }
    }
    return FALSE;
  }

  /**
   * Is CI.
   */
  public static function isCiEnv() {
    return self::getCiEnv() || isset($_ENV['CI']);
  }

  /**
   * Is Pantheon.
   */
  public static function isPantheonEnv() {
    return isset($_ENV['PANTHEON_ENVIRONMENT']);
  }

  /**
   * Get Pantheon.
   */
  public static function getPantheonEnv() {
    return self::isPantheonEnv() ? $_ENV['PANTHEON_ENVIRONMENT'] : NULL;
  }

  /**
   * Is Pantheon.
   */
  public static function isPantheonDevEnv() {
    return self::getPantheonEnv() == 'dev';
  }

  /**
   * Is Pantheon.
   */
  public static function isPantheonStageEnv() {
    return self::getPantheonEnv() == 'test';
  }

  /**
   * Is Pantheon.
   */
  public static function isPantheonProdEnv() {
    return self::getPantheonEnv() == 'live';
  }

  /**
   * Is local.
   */
  public static function isLocalEnv() {
    return !self::isAhEnv() && !self::isPantheonEnv() && !self::isCiEnv();
  }

  /**
   * Is dev.
   */
  public static function isDevEnv() {
    return self::isAhDevEnv() || self::isPantheonDevEnv();
  }

  /**
   * Is stage.
   */
  public static function isStageEnv() {
    return self::isAhStageEnv() || self::isPantheonStageEnv();
  }

  /**
   * Is prod.
   */
  public static function isProdEnv() {
    return self::isAhProdEnv() || self::isPantheonProdEnv();
  }

  /**
   * Get AH files.
   */
  public static function getAhFilesRoot() {
    return '/mnt/files/' . self::getAhGroup() . '.' . self::getAhEnv();
  }

  /**
   * Is ACSF.
   */
  public static function isAcsfInited() {
    return file_exists(DRUPAL_ROOT . "/sites/g");
  }

  /**
   * Get ACSF db.
   *
   * @return string|null
   *   ACSF db name.
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  public static function getAcsfDbName() {
    return isset($GLOBALS['gardens_site_settings']) && self::isAcsfEnv() ? $GLOBALS['gardens_site_settings']['conf']['acsf_db_name'] : NULL;
  }

}
