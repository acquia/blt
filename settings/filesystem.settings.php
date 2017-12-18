<?php

/**
 * @file
 * Contains filesystem settings.
 */

// Local file paths.
$settings['file_public_path'] = "sites/$site_dir/files";
$settings['file_private_path'] = "$repo_root/files-private/$site-dir";
if (isset($acsf_site_name)) {
  $settings['file_public_path'] = "sites/default/files/$acsf_site_name";
  $settings['file_private_path'] = "$repo_root/files-private/$acsf_site_name";
}

// ACSF file paths.
if ($is_acsf_env) {
  $settings['file_public_path'] = "sites/g/files/$acsf_db_name/files";
  $settings['file_private_path'] = "/mnt/files/$ah_group.$ah_env/sites/g/files-private/$acsf_db_name";
}
// Acquia cloud file paths.
elseif ($is_ah_env) {
  $settings['file_private_path'] = "/mnt/files/$ah_group.$ah_env/files-private";
}
