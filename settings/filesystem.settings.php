<?php

/**
 * @file
 * Contains filesystem settings.
 */

$settings['file_public_path'] = "sites/$site_dir/files";

// ACSF file paths.
if ($is_acsf) {
  $settings['file_public_path'] = "sites/g/files/$acsf_db_name/files";
  $settings['file_private_path'] = "/mnt/files/$ah_group.$ah_env/sites/g/files-private/$acsf_db_name";
}
// Acquia cloud file paths.
elseif ($is_ah_env) {
  $config['system.file']['path']['temporary'] = '/mnt/tmp/' . $_ENV['AH_SITE_NAME'];
  $settings['file_private_path'] = "/mnt/files/$ah_group.$ah_env/files-private";
}
