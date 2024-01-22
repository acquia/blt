<?php

/**
 * @file
 * Controls configuration management settings.
 */

use Acquia\Blt\Robo\Common\EnvironmentDetector;

/**
 * Override config directories.
 *
 * BLT makes the assumption that, if using multisite, the default configuration
 * directory should be shared between all multi-sites, and each multisite will
 * override this selectively using configuration splits. However, some
 * applications may prefer to manage the configuration for each multisite
 * completely separately. If this is the case, they can
 * set FALSE to $drs_override_config_directories
 * and $drs_override_site_studio_sync_directories
 * $settings['config_sync_directory'] = $dir . "/config/$site_dir"
 * $settings['site_studio_sync'] =  $dir . "/sitestudio/$site_dir" in
 * settings.php, and we will not overwrite it.
 */
// phpcs:ignore
$drs_override_config_directories = !$drs_override_config_directories ?: TRUE;
$drs_override_site_studio_sync_directories = !$drs_override_site_studio_sync_directories ?: TRUE;

/**
 * Site path.
 *
 * @var string $site_path
 * This is always set and exposed by the Drupal Kernel.
 */
// phpcs:ignore
$site_name = EnvironmentDetector::getSiteName($site_path);

// phpcs:ignore
// Config sync settings.
$settings['config_sync_directory'] = $drs_override_config_directories ?
"../config/$site_name" : "../config/default";
// Site Studio sync settings.
$settings['site_studio_sync'] = $drs_override_site_studio_sync_directories ?
"../sitestudio/$site_name" : "../sitestudio/default";

$split_filename_prefix = 'config_split.config_split';

/**
 * Set environment splits.
 */
$split_envs = EnvironmentDetector::getEnvironments();
foreach ($split_envs as $split_env => $status) {
  $config["$split_filename_prefix.$split_env"]['status'] = $status;
}

/**
 * Set multisite split.
 */
/**
 * Site path.
 *
 * @var string $site_path
 * This is always set and exposed by the Drupal Kernel.
 */
// phpcs:ignore
$site_name = EnvironmentDetector::getSiteName($site_path);
// phpcs:ignore
$config["$split_filename_prefix.$site_name"]['status'] = TRUE;

// Set acsf site split if explicit global exists.
global $_acsf_site_name;
if (isset($_acsf_site_name)) {
  $config["$split_filename_prefix.$_acsf_site_name"]['status'] = TRUE;
}