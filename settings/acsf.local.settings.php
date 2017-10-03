<?php

/**
 * @file
 * Contains ACSF configuration.
 */

if (isset($acsf_site_name)) {
  // Database configuration.
  $databases = array(
    'default' =>
      array(
        'default' =>
          array(
            'database' => "drupal_{$acsf_site_name}",
            'username' => 'drupal',
            'password' => 'drupal',
            'host' => 'localhost',
            'port' => '3306',
            'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
            'driver' => 'mysql',
            'prefix' => '',
          ),
      ),
  );

  $settings['file_public_path'] = "sites/default/files/$acsf_site_name";
  $settings['file_private_path'] = "$repo_root/files-private/$acsf_site_name";
}
