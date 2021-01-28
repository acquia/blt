<?php

/**
 * @file
 * Contains filesystem settings.
 */

use Acquia\Blt\Robo\Common\EnvironmentDetector;
use Acquia\Blt\Robo\Exceptions\BltException;

/**
 * Site path.
 *
 * @var string $site_path
 * This is always set and exposed by the Drupal Kernel.
 */
// phpcs:ignore
$settings['file_public_path'] = "sites/" . EnvironmentDetector::getSiteName($site_path) . "/files";

try {
  $acsf_db_name = EnvironmentDetector::getAcsfDbName();
  $is_acsf_env = EnvironmentDetector::isAcsfEnv();
}
catch (BltException $exception) {
  trigger_error($exception->getMessage(), E_USER_WARNING);
}

if ($is_acsf_env && $acsf_db_name) {
  // ACSF file paths.
  $settings['file_public_path'] = "sites/g/files/$acsf_db_name/files";
  $settings['file_private_path'] = EnvironmentDetector::getAhFilesRoot() . "/sites/g/files-private/$acsf_db_name";
}
elseif (EnvironmentDetector::isAhEnv()) {
  // Acquia cloud file paths.
  /**
   * Site path.
   *
   * @var string $site_path
   * This is always set and exposed by the Drupal Kernel.
   */
  // phpcs:ignore
  $settings['file_private_path'] = EnvironmentDetector::getAhFilesRoot() . "/sites/" . EnvironmentDetector::getSiteName($site_path) . "/files-private";
}
