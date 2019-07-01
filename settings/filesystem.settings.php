<?php

/**
 * @file
 * Contains filesystem settings.
 */

use Acquia\Blt\Robo\Common\EnvironmentDetector;
use Acquia\Blt\Robo\Exceptions\BltException;

// phpcs:ignore
$settings['file_public_path'] = "sites/$site_dir/files";

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
  // phpcs:ignore
  $settings['file_private_path'] = EnvironmentDetector::getAhFilesRoot() . "/sites/$site_dir/files-private";
}
