<?php

/**
 * @file
 * Contains filesystem settings.
 */

$settings['file_public_path'] = 'sites/default/files';

/**
 * Acquia cloud file paths.
 */
if ($is_ah_env) {
  $config['system.file']['path']['temporary'] = '/mnt/tmp/' . $_ENV['AH_SITE_NAME'];
  $settings['file_private_path'] = '/mnt/files/' . $_ENV['AH_SITE_NAME'] . '/files-private';
}
