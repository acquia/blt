<?php

namespace Acquia\Blt\Robo\Common;

use drupol\phposinfo\OsInfo;

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
   * Check if this is an ACSF env.
   *
   * Roughly duplicates the detection logic implemented by the ACSF module.
   *
   * @param mixed $ah_group
   *   The Acquia Hosting site / group name (e.g. my_subscription).
   * @param mixed $ah_env
   *   The Acquia Hosting environment name (e.g. 01dev).
   *
   * @return bool
   *   TRUE if this is an ACSF environment, FALSE otherwise.
   *
   * @see https://git.drupalcode.org/project/acsf/blob/8.x-2.62/acsf_init/lib/sites/default/acsf.settings.php#L14
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

    return file_exists("/mnt/files/$ah_group.$ah_env/files-private/sites.json");
  }

  /**
   * Is AH prod.
   */
  public static function isAhProdEnv() {
    $ah_env = self::getAhEnv();
    // ACE prod is 'prod'; ACSF can be '01live', '02live', ...
    return $ah_env == 'prod' || preg_match('/^\d*live$/', $ah_env);
  }

  /**
   * Is AH stage.
   */
  public static function isAhStageEnv() {
    $ah_env = self::getAhEnv();
    // ACE staging is 'test' or 'stg'; ACSF is '01test', '02test', ...
    return preg_match('/^\d*test$/', $ah_env) || $ah_env == 'stg';
  }

  /**
   * Is AH dev.
   */
  public static function isAhDevEnv() {
    // ACE dev is 'dev', 'dev1', ...; ACSF dev is '01dev', '02dev', ...
    return (preg_match('/^\d*dev\d*$/', self::getAhEnv()));
  }

  /**
   * Is AH ODE.
   */
  public static function isAhOdeEnv($ah_env = NULL) {
    if (is_null($ah_env)) {
      $ah_env = self::getAhEnv();
    }
    // CDEs (formerly 'ODEs') can be 'ode1', 'ode2', ...
    return (preg_match('/^ode\d*$/', $ah_env));
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
   * Get CI env name.
   *
   * In the case of multiple environment detectors declaring a CI env name, the
   * first one wins.
   */
  public static function getCiEnv() {
    $results = self::getSubclassResults(__FUNCTION__);
    if ($results) {
      return current($results);
    }

    $mapping = [
      'TRAVIS' => 'travis',
      'PIPELINE_ENV' => 'pipelines',
      'PROBO_ENVIRONMENT' => 'probo',
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
   * Get the settings file include for the current CI environment.
   *
   * This may be provided by BLT, or via a Composer package that has provided
   * its own environment detector. In the case of multiple detectors providing a
   * settings file, the first one wins.
   *
   * @return string
   *   Settings file full path and filename.
   */
  public static function getCiSettingsFile() {
    $results = array_filter(self::getSubclassResults(__FUNCTION__));
    if ($results) {
      return current($results);
    }

    return sprintf("%s/vendor/acquia/blt/settings/%s.settings.php", dirname(DRUPAL_ROOT), self::getCiEnv());
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
    $results = self::getSubclassResults(__FUNCTION__);
    if ($results) {
      return TRUE;
    }

    return self::isAhDevEnv() || self::isPantheonDevEnv();
  }

  /**
   * Is stage.
   */
  public static function isStageEnv() {
    $results = self::getSubclassResults(__FUNCTION__);
    if ($results) {
      return TRUE;
    }

    return self::isAhStageEnv() || self::isPantheonStageEnv();
  }

  /**
   * Is prod.
   */
  public static function isProdEnv() {
    $results = self::getSubclassResults(__FUNCTION__);
    if ($results) {
      return TRUE;
    }

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
   * OS is Darwin.
   */
  public static function isDarwin() {
    return OsInfo::isApple();
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

  /**
   * Call a given function in all EnvironmentDetector subclasses.
   *
   * Composer packages can provide their own version of an EnvironmentDetector
   * that inherits from this one. This allows for detection of new types of
   * environments not hardcoded in this class.
   *
   * @param string $functionName
   *   The function name to call in a subclass.
   *
   * @return array
   *   Results from each subclass function call (omits any null / false results)
   *
   * @throws \ReflectionException
   */
  private static function getSubclassResults($functionName) {
    $autoloader = require DRUPAL_ROOT . '/autoload.php';
    $classMap = $autoloader->getClassMap();
    $detectors = array_filter($classMap, function ($classPath) {
      return strpos($classPath, 'Blt/Plugin/EnvironmentDetector') !== FALSE;
    });
    $results = [];
    foreach ($detectors as $detector => $classPath) {
      // Only call this method if it's been overridden by the child class.
      $detectorReflector = new \ReflectionMethod($detector, $functionName);
      if ($detectorReflector->getDeclaringClass()->getName() === $detector) {
        $results[] = call_user_func([$detector, $functionName]);
      }
    }
    return array_filter($results);
  }

}
