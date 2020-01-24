<?php

/**
 * @file
 * Common settings for CI envs.
 */

$config['system.logging']['error_level'] = 'verbose';

$dir = dirname(DRUPAL_ROOT);
$settings['file_private_path'] = $dir . '/files-private';
$settings['trusted_host_patterns'] = [
  '^.+$',
];

/**
 * Sensible CI defaults for databases.
 *
 * This will be overridden by system specific CI files.
 */
$databases = [
  'default' => [
    'default' => [
      'database' => 'drupal',
      'username' => 'drupal',
      'password' => 'drupal',
      'host' => '127.0.0.1',
      'port' => '3306',
      'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
      'driver' => 'mysql',
      'prefix' => '',
    ],
  ],
];
