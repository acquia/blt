<?php

// BLT makes the assumption that, if using multisite, the default configuration
// directory should be shared between all multi-sites, and each multisite will
// override this selectively using configuration splits. However, some
// applications may prefer to manage the configuration for each multisite
// completely separately. If this is the case, they can set
// $config_directories['sync'] = $dir . "/config/$site_dir" and we will not
// overwrite it.
if (!isset($config_directories['sync'])) {
  // Configuration directories.
  $dir = dirname(DRUPAL_ROOT);
  $config_directories['sync'] = $dir . "/config/default";
}

$split_filename_prefix = 'config_split.config_split';
$split_filepath_prefix = $config_directories['sync']  . '/' . $split_filename_prefix;
$split_envs = array('local', 'dev', 'stage', 'prod', 'ci', 'ah_other');
// Ensure the appropriate config split is enabled.
foreach ($split_envs as $split_env) {
  $config["$split_filename_prefix.$split_env"]['status'] = FALSE;
}

// Set env split.
// Do not set $split unless it is unset. This allows prior scripts to set it.
if (!isset($split)) {
  $split = 'none';

  // Non-acquia envs.
  if ($is_local_env) {
    $split = 'local';
    if (getenv('TRAVIS') || getenv('PIPELINE_ENV') || getenv('PROBO_ENVIRONMENT')) {
      $split = 'ci';
    }
  }
  // Acquia only envs.
  elseif ($is_ah_env) {
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
}

if ($split != 'none' && file_exists("$split_filepath_prefix.$split.yml")) {
  $config["$split_filename_prefix.$split"]['status'] = TRUE;
}

// Set multisite split.
if (file_exists("$split_filepath_prefix.$site_dir.yml")) {
  $config["$split_filename_prefix.$site_dir"]['status'] = TRUE;
}