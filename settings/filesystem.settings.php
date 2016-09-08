<?php

/**
 * @file
 * Contains filesystem settings.
 */

$settings['file_public_path'] = 'sites/default/files';

// ACSF file paths.
if ($is_acsf) {
  $settings['file_private_path'] = "/mnt/gfs/$ah_group.$ah_env/files-private/$acsf_db_name";
}
// Acquia cloud file paths.
elseif ($is_ah_env) {
  $config['system.file']['path']['temporary'] = '/mnt/tmp/' . $_ENV['AH_SITE_NAME'];
  $settings['file_private_path'] = "/mnt/files/$ah_group.$ah_env/files-private";
}
