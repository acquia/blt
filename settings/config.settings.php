<?php

/**
 * @file
 * Controls configuration management settings.
 */

/**
 * BLT makes the assumption that, if using multisite, the default configuration
 * directory should be shared between all multi-sites, and each multisite will
 * override this selectively using configuration splits. However, some
 * applications may prefer to manage the configuration for each multisite
 * completely separately. If this is the case, they can set
 * $blt_override_config_directories to FALSE and
 * $config_directories['sync'] = $dir . "/config/$site_dir" in settings.php,
 * and we will not overwrite it.
 */
if (!isset($blt_override_config_directories)) {
  $blt_override_config_directories = TRUE;
}

// Configuration directories.
if ($blt_override_config_directories) {
  $config_directories['sync'] = $repo_root . "/config/default";
}

$split_filename_prefix = 'config_split.config_split';
$split_filepath_prefix = $config_directories['sync'] . '/' . $split_filename_prefix;

/**
 * Set environment splits.
 */
$split_envs = [
  'local',
  'dev',
  'stage',
  'prod',
  'ci',
  'ah_other',
];

// Disable all split by default.
foreach ($split_envs as $split_env) {
  $config["$split_filename_prefix.$split_env"]['status'] = FALSE;
}

// Enable env splits.
// Do not set $split unless it is unset. This allows prior scripts to set it.
if (!isset($split)) {
  $split = 'none';

  // Local envs.
  if ($is_local_env) {
    $split = 'local';
  }
  // CI envs.
  if ($is_ci_env) {
    $split = 'ci';
  }
  // Acquia only envs.
  if ($is_ah_env) {
    $config_directories['vcs'] = $config_directories['sync'];

    $split = 'ah_other';
    if ($is_ah_dev_env || $is_ah_ode_env) {
      $split = 'dev';
    }
    elseif ($is_ah_stage_env) {
      $split = 'stage';
    }
    elseif ($is_ah_prod_env) {
      $split = 'prod';
    }
  }
  elseif ($is_pantheon_env) {
    if ($pantheon_env == 'live') {
      $split = 'prod';
    }
    elseif ($pantheon_env == 'test') {
      $split = 'stage';
    }
    elseif ($pantheon_env == 'dev') {
      $split = 'dev';
    }
  }
}

// Enable the environment split only if it exists.
if ($split != 'none') {
  $config["$split_filename_prefix.$split"]['status'] = TRUE;
}

/**
 * Set multisite split.
 */
$config["$split_filename_prefix.$site_dir"]['status'] = TRUE;

// Set acsf site split if explicit global exists.
if (isset($acsf_site_name)) {
  $config["$split_filename_prefix.$acsf_site_name"]['status'] = TRUE;
}

// Set profile split.
if (array_key_exists('install_profile', $settings)) {
  $active_profile = $settings['install_profile'];
  $config["$split_filename_prefix.$active_profile"]['status'] = TRUE;
}
