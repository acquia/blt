<?php

namespace Acquia\Blt\Robo\Common;

use Acquia\Blt\Robo\Exceptions\BltException;

/**
 * Class EnvironmentDetector
 * @package Acquia\Blt\Robo\Common
 *
 * Attempts to detect various properties about the current hosting environment.
 */
class EnvironmentDetector
{
  public static function isAhEnv() {
    return (bool) self::getAhEnv();
  }

  /**
   * @param null $ah_group
   *   The Acquia Hosting site / group name (e.g. my_subscription).
   * @param null $ah_env
   *   The Acquia Hosting environment name (e.g. 01dev).
   * @return bool
   * @throws BltException
   */
  public static function isAcsfEnv($ah_group = null, $ah_env = null) {
    if (is_null($ah_group)) {
      $ah_group = self::getAhGroup();
    }

    if (is_null($ah_env)) {
      $ah_env = self::getAhEnv();
    }

    if (empty($ah_group) || empty($ah_env)) {
      return false;
    }

    $is_acsf_json = file_exists("/mnt/files/$ah_group.$ah_env/files-private/sites.json");
    $is_acsf_env_name = preg_match('/01(dev|test|live|update)(up)?/', $ah_env);

    if ($is_acsf_json != $is_acsf_env_name) {
      throw new BltException("Cannot determine if this is an ACSF environment or not.");
    }

    return ($is_acsf_env_name && $is_acsf_json);
  }

  public static function isAhProdEnv() {
    $ah_env = self::getAhEnv();
    // ACE prod is 'prod'; ACSF can be '01live', '02live', ...
    return $ah_env == 'prod' || preg_match('/^\d*live$/', $ah_env);
  }

  public static function isAhStageEnv() {
    $ah_env = self::getAhEnv();
    // ACE staging is 'test' or 'stg'; ACSF is '01test', '02test', ...
    return preg_match('/^\d*test$/', $ah_env) || $ah_env == 'stg';
  }

  public static function isAhDevEnv() {
    // ACE dev is 'dev', 'dev1', ...; ACSF dev is '01dev', '02dev', ...
    return (preg_match('/^\d*dev\d*$/', self::getAhEnv()));
  }

  public static function isAhOdeEnv($ah_env = null) {
    if (is_null($ah_env)) {
      $ah_env = self::getAhEnv();
    }
    // CDEs (formerly 'ODEs') can be 'ode1', 'ode2', ...
    return (preg_match('/^ode\d*$/', $ah_env));
  }

  public static function isAhDevCloud() {
    return (!empty($_SERVER['HTTP_HOST']) && strstr($_SERVER['HTTP_HOST'], 'devcloud'));
  }

  public static function getAhGroup() {
    return isset($_ENV['AH_SITE_GROUP']) ? $_ENV['AH_SITE_GROUP'] : NULL;
  }

  public static function getAhEnv() {
    return isset($_ENV['AH_SITE_ENVIRONMENT']) ? $_ENV['AH_SITE_ENVIRONMENT'] : NULL;
  }

  public static function getAhSite() {
    return isset($_ENV['AH_SITE_NAME']) ? $_ENV['AH_SITE_NAME'] : NULL;
  }
}
