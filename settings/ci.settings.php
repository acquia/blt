<?php

/**
 * @file
 * Common settings for CI envs.
 */

$config['system.logging']['error_level'] = 'verbose';
$config['system.file']['path']['temporary'] = '/tmp';

$dir = dirname(DRUPAL_ROOT);
$settings['file_private_path'] = $dir . '/files-private';
$settings['trusted_host_patterns'] = array(
  '^.+$',
);
