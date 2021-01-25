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
$databases['default']['default'] = [
  'database' => 'drupal',
  'username' => 'drupal',
  'password' => 'drupal',
  'host' => '127.0.0.1',
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
  'prefix' => '',
];

/**
 * Skip file system permissions hardening.
 *
 * The system module will periodically check the permissions of your site's
 * site directory to ensure that it is not writable by the website user. For
 * sites that are managed with a version control system, this can cause problems
 * when files in that directory such as settings.php are updated, because the
 * user pulling in the changes won't have permissions to modify files in the
 * directory.
 */
$settings['skip_permissions_hardening'] = TRUE;
