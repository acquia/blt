<?php

// Configuration directories.
$dir = dirname(DRUPAL_ROOT);
$config_directories['sync'] = $dir . "/config/$site_dir";
$split_filename_prefix = 'config_split.config_split';
$split_filepath_prefix = $config_directories['sync']  . '/' . $split_filename_prefix;

// Ensure the appropriate config split is enabled.
$config['config_split.config_split.local']['status'] = FALSE;
$config['config_split.config_split.dev']['status'] = FALSE;
$config['config_split.config_split.stage']['status'] = FALSE;
$config['config_split.config_split.prod']['status'] = FALSE;
$config['config_split.config_split.ci']['status'] = FALSE;
$config['config_split.config_split.ah_other']['status'] = FALSE;

// Non-acquia envs.
if ($is_local_env) {
  if (getenv('TRAVIS') || getenv('PIPELINE_ENV')) {
    $split = 'ci';
    if (file_exists("$split_filepath_prefix.$split.yml")) {
      $config["$split_filename_prefix.$split"]['status'] = TRUE;
    }
  }
  else {
    $split = 'local';
    if (file_exists("$split_filepath_prefix.$split.yml")) {
      $config["$split_filename_prefix.$split"]['status'] = TRUE;
    }
  }
}
// Acquia only envs.
elseif ($is_ah_env) {
  $config_directories['vcs'] = $config_directories['sync'];

  if ($is_ah_dev_env) {
    $split = 'dev';
    if (file_exists("$split_filepath_prefix.$split.yml")) {
      $config["$split_filename_prefix.$split"]['status'] = TRUE;
    }
  }
  elseif ($is_ah_stage_env) {
    $split = 'stage';
    if (file_exists("$split_filepath_prefix.$split.yml")) {
      $config["$split_filename_prefix.$split"]['status'] = TRUE;
    }
  }
  elseif ($is_ah_prod_env) {
    $split = 'prod';
    if (file_exists("$split_filepath_prefix.$split.yml")) {
      $config["$split_filename_prefix.$split"]['status'] = TRUE;
    }
  }
  else {
    $split = 'ah_other';
    if (file_exists("$split_filepath_prefix.$split.yml")) {
      $config["$split_filename_prefix.$split"]['status'] = TRUE;
    }
  }
}
