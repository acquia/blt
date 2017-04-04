<?php

// Configuration directories.
$dir = dirname(DRUPAL_ROOT);
$config_directories['sync'] = $dir . "/config/$site_dir";

// Ensure the appropriate config split is enabled.
$config['config_split.config_split.local']['status'] = FALSE;
$config['config_split.config_split.dev']['status'] = FALSE;
$config['config_split.config_split.stage']['status'] = FALSE;
$config['config_split.config_split.prod']['status'] = FALSE;
$config['config_split.config_split.ci']['status'] = FALSE;

if ($is_local_env) {
  if (getenv('TRAVIS')) {
    $config['config_split.config_split.ci']['status'] = TRUE;
  }
  else {
    $config['config_split.config_split.local']['status'] = TRUE;
  }
}
else {
  $config_directories['vcs'] = $config_directories['sync'];

  if ($is_ah_dev_env) {
    $config['config_split.config_split.dev']['status'] = TRUE;
  }
  elseif ($is_ah_stage_env) {
    $config['config_split.config_split.stage']['status'] = TRUE;
    $config['config_split.config_split.test']['status'] = TRUE;
  }
  elseif ($is_ah_prod_env) {
    $config['config_split.config_split.prod']['status'] = TRUE;
  }
}
