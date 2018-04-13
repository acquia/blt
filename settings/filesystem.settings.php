<?php

/**
 * @file
 * Contains filesystem settings.
 */

$settings['file_public_path'] = "sites/$site_dir/files";

// ACSF file paths.
if ($is_acsf_env && $acsf_db_name) {
  $settings['file_public_path'] = "sites/g/files/$acsf_db_name/files";
  $settings['file_private_path'] = "/mnt/files/$ah_group.$ah_env/sites/g/files-private/$acsf_db_name";
}
// Acquia cloud file paths.
elseif ($is_ah_env) {
  $settings['file_private_path'] = "/mnt/files/$ah_group.$ah_env/files-private";
}
