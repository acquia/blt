<?php

/**
 * @file
 * Contains Files configuration.
 */
if ($is_acsf) {
  $settings['file_private_path'] = "/mnt/gfs/$ah_group.$ah_env/files-private/$acsf_db_name";
}
else {
  $settings['file_private_path'] = "/mnt/files/$ah_group.$ah_env/files-private";
}
